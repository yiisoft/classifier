<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

/**
 * This file was copied from {@link https://github.com/spiral/tokenizer}.
 *
 * @internal
 *
 * @psalm-type TPosition = list{int, int}
 */
final class ReflectionFile
{
    /**
     * Namespace separator.
     */
    public const NS_SEPARATOR = '\\';

    /**
     * Opening and closing token ids.
     */
    public const O_TOKEN = 0;
    public const C_TOKEN = 1;

    public const T_OPEN_CURLY_BRACES = 123;
    public const T_CLOSE_CURLY_BRACES = 125;
    public const T_SEMICOLON = 59;

    /**
     * Set of tokens required to detect classes, traits, interfaces declarations. We
     * don't need any other token for that.
     */
    private const TOKENS = [
        self::T_OPEN_CURLY_BRACES,
        self::T_CLOSE_CURLY_BRACES,
        self::T_SEMICOLON,
        T_PAAMAYIM_NEKUDOTAYIM,
        T_NAMESPACE,
        T_STRING,
        T_CLASS,
        T_INTERFACE,
        T_TRAIT,
        T_ENUM,
        T_NS_SEPARATOR,
    ];

    /**
     * Parsed tokens array.
     *
     * @var array<int, \PhpToken>
     */
    private array $tokens;

    /**
     * Total tokens count.
     */
    private int $countTokens;

    /**
     * Namespaces used in file and their token positions.
     *
     * @psalm-var array<string, TPosition>
     */
    private array $namespaces = [];

    /**
     * Declarations of classes, interfaces and traits.
     *
     * @psalm-var array<class-string|trait-string, TPosition>
     */
    private array $declarations = [];

    public function __construct(
        private string $filename
    ) {
        $this->tokens = \PhpToken::tokenize(file_get_contents($this->filename));
        $this->countTokens = \count($this->tokens);

        //Looking for declarations
        $this->locateDeclarations();
    }

    /**
     * List of declarations names
     *
     * @return array<class-string|trait-string>
     */
    public function getDeclarations(): array
    {
        return \array_keys($this->declarations);
    }

    /**
     * Locate every class, interface, trait or enum definition.
     */
    private function locateDeclarations(): void
    {
        foreach ($this->tokens as $tokenIndex => $token) {
            if (!\in_array($token->id, self::TOKENS, true)) {
                continue;
            }

            switch ($token->id) {
                case T_NAMESPACE:
                    $this->registerNamespace($tokenIndex);
                    break;

                case T_CLASS:
                case T_TRAIT:
                case T_INTERFACE:
                case T_ENUM:
                    if ($this->isClassNameConst($tokenIndex)) {
                        // PHP5.5 ClassName::class constant
                        continue 2;
                    }

                    if ($this->isAnonymousClass($tokenIndex)) {
                        // PHP7.0 Anonymous classes new class ('foo', 'bar')
                        continue 2;
                    }

                    if (!$this->isCorrectDeclaration($tokenIndex)) {
                        // PHP8.0 Named parameters ->foo(class: 'bar')
                        continue 2;
                    }

                    $this->registerDeclaration($tokenIndex);
                    break;
            }
        }

        //Dropping empty namespace
        if (isset($this->namespaces[''])) {
            $this->namespaces['\\'] = $this->namespaces[''];
            unset($this->namespaces['']);
        }
    }

    /**
     * Handle namespace declaration.
     */
    private function registerNamespace(int $tokenIndex): void
    {
        $namespace = '';
        $localIndex = $tokenIndex + 1;

        do {
            $token = $this->tokens[$localIndex++];
            $namespace .= $token->text;
        } while (
            isset($this->tokens[$localIndex])
            && $this->tokens[$localIndex]->text !== '{'
            && $this->tokens[$localIndex]->text !== ';'
        );

        //Whitespaces
        $namespace = \trim($namespace);

        if ($this->tokens[$localIndex]->text === ';') {
            $endingIndex = \count($this->tokens) - 1;
        } else {
            $endingIndex = $this->endingToken($tokenIndex);
        }

        $this->namespaces[$namespace] = [
            self::O_TOKEN => $tokenIndex,
            self::C_TOKEN => $endingIndex,
        ];
    }

    /**
     * Handle declaration of class, trait of interface. Declaration will be stored under it's token
     * type in declarations array.
     */
    private function registerDeclaration(int $tokenIndex): void
    {
        $localIndex = $tokenIndex + 1;
        while ($this->tokens[$localIndex]->id !== T_STRING) {
            ++$localIndex;
        }

        $name = $this->tokens[$localIndex]->text;
        if (!empty($namespace = $this->activeNamespace($tokenIndex))) {
            $name = $namespace . self::NS_SEPARATOR . $name;
        }

        /** @var class-string|trait-string $name */
        $this->declarations[$name] = [
            self::O_TOKEN => $tokenIndex,
            self::C_TOKEN => $this->endingToken($tokenIndex),
        ];
    }

    /**
     * Check if token ID represents `ClassName::class` constant statement.
     */
    private function isClassNameConst(int $tokenIndex): bool
    {
        return $this->tokens[$tokenIndex]->id === T_CLASS
            && isset($this->tokens[$tokenIndex - 1])
            && $this->tokens[$tokenIndex - 1]->id === T_PAAMAYIM_NEKUDOTAYIM;
    }

    /**
     * Check if token ID represents anonymous class creation, e.g. `new class ('foo', 'bar')`.
     */
    private function isAnonymousClass(int $tokenIndex): bool
    {
        return $this->tokens[$tokenIndex]->id === T_CLASS
            && isset($this->tokens[$tokenIndex - 2])
            && $this->tokens[$tokenIndex - 2]->id === T_NEW;
    }

    /**
     * Check if token ID represents named parameter with name `class`, e.g. `foo(class: SomeClass::name)`.
     */
    private function isCorrectDeclaration(int $tokenIndex): bool
    {
        return \in_array($this->tokens[$tokenIndex]->id, [T_CLASS, T_TRAIT, T_INTERFACE, T_ENUM], true)
            && isset($this->tokens[$tokenIndex + 2])
            && $this->tokens[$tokenIndex + 1]->id === T_WHITESPACE
            && $this->tokens[$tokenIndex + 2]->id === T_STRING;
    }

    /**
     * Get namespace name active at specified token position.
     *
     * @return array-key
     */
    private function activeNamespace(int $tokenIndex): string
    {
        foreach ($this->namespaces as $namespace => $position) {
            if ($tokenIndex >= $position[self::O_TOKEN] && $tokenIndex <= $position[self::C_TOKEN]) {
                return $namespace;
            }
        }

        //Seems like no namespace declaration
        $this->namespaces[''] = [
            self::O_TOKEN => 0,
            self::C_TOKEN => \count($this->tokens),
        ];

        return '';
    }

    /**
     * Find token index of ending brace.
     */
    private function endingToken(int $tokenIndex): int
    {
        $level = 0;
        $hasOpen = false;
        for ($localIndex = $tokenIndex; $localIndex < $this->countTokens; ++$localIndex) {
            $token = $this->tokens[$localIndex];
            if ($token->text === '{') {
                ++$level;
                $hasOpen = true;
                continue;
            }

            if ($token->text === '}') {
                --$level;
            }

            if ($hasOpen && $level === 0) {
                break;
            }
        }

        return $localIndex;
    }
}
