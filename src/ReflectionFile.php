<?php

declare(strict_types=1);

namespace Yiisoft\Classifier;

/**
 * This file was copied from {@link https://github.com/spiral/tokenizer}.
 *
 * @internal
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


    /**
     * Set of tokens required to detect classes, traits, interfaces declarations. We
     * don't need any other token for that.
     */
    private const TOKENS = [
        '{',
        '}',
        ';',
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
     */
    private array $tokens;

    /**
     * Total tokens count.
     */
    private int $countTokens;

    /**
     * Namespaces used in file and their token positions.
     *
     * @internal
     */
    private array $namespaces = [];

    /**
     * Declarations of classes, interfaces and traits.
     *
     * @internal
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
     * Filename.
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * List of declarations names
     */
    public function getDeclarations(): array
    {
        return \array_keys($this->declarations);
    }

    /**
     * Get list of tokens associated with given file.
     *
     * @return \PhpToken[]
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    /**
     * Locate every class, interface, trait or enum definition.
     */
    private function locateDeclarations(): void
    {
        foreach ($this->getTokens() as $tokenID => $token) {
            if ($token->isIgnorable() || !\in_array($token->id, self::TOKENS, true)) {
                continue;
            }

            switch ($token->id) {
                case T_NAMESPACE:
                    $this->registerNamespace($tokenID);
                    break;

                case T_CLASS:
                case T_TRAIT:
                case T_INTERFACE:
                case T_ENUM:
                    if ($this->isClassNameConst($tokenID)) {
                        // PHP5.5 ClassName::class constant
                        continue 2;
                    }

                    if ($this->isAnonymousClass($tokenID)) {
                        // PHP7.0 Anonymous classes new class ('foo', 'bar')
                        continue 2;
                    }

                    if (!$this->isCorrectDeclaration($tokenID)) {
                        // PHP8.0 Named parameters ->foo(class: 'bar')
                        continue 2;
                    }

                    $this->registerDeclaration($tokenID, $token->getTokenName() ?? $token->text);
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
    private function registerNamespace(int $tokenID): void
    {
        $namespace = '';
        $localID = $tokenID + 1;

        do {
            $token = $this->tokens[$localID++];
            if ($token->text === '{') {
                break;
            }

            $namespace .= $token->text;
        } while (
            isset($this->tokens[$localID])
            && $this->tokens[$localID]->text !== '{'
            && $this->tokens[$localID]->text !== ';'
        );

        //Whitespaces
        $namespace = \trim($namespace);

        $uses = $this->namespaces[$namespace] ?? [];

        if ($this->tokens[$localID]->text === ';') {
            $endingID = \count($this->tokens) - 1;
        } else {
            $endingID = $this->endingToken($tokenID);
        }

        $this->namespaces[$namespace] = [
            self::O_TOKEN => $tokenID,
            self::C_TOKEN => $endingID,
        ];
    }

    /**
     * Handle declaration of class, trait of interface. Declaration will be stored under it's token
     * type in declarations array.
     */
    private function registerDeclaration(int $tokenID): void
    {
        $localID = $tokenID + 1;
        while ($this->tokens[$localID]->id !== T_STRING) {
            ++$localID;
        }

        $name = $this->tokens[$localID]->text;
        if (!empty($namespace = $this->activeNamespace($tokenID))) {
            $name = $namespace . self::NS_SEPARATOR . $name;
        }

        $this->declarations[$name] = [
            self::O_TOKEN => $tokenID,
            self::C_TOKEN => $this->endingToken($tokenID),
        ];
    }

    /**
     * Check if token ID represents `ClassName::class` constant statement.
     */
    private function isClassNameConst(int $tokenID): bool
    {
        return $this->tokens[$tokenID]->id === T_CLASS
            && isset($this->tokens[$tokenID - 1])
            && $this->tokens[$tokenID - 1]->id === T_PAAMAYIM_NEKUDOTAYIM;
    }

    /**
     * Check if token ID represents anonymous class creation, e.g. `new class ('foo', 'bar')`.
     */
    private function isAnonymousClass(int|string $tokenID): bool
    {
        return $this->tokens[$tokenID]->id === T_CLASS
            && isset($this->tokens[$tokenID - 2])
            && $this->tokens[$tokenID - 2]->id === T_NEW;
    }

    /**
     * Check if token ID represents named parameter with name `class`, e.g. `foo(class: SomeClass::name)`.
     */
    private function isCorrectDeclaration(int|string $tokenID): bool
    {
        return \in_array($this->tokens[$tokenID]->id, [T_CLASS, T_TRAIT, T_INTERFACE, T_ENUM], true)
            && isset($this->tokens[$tokenID + 2])
            && $this->tokens[$tokenID + 1]->id === T_WHITESPACE
            && $this->tokens[$tokenID + 2]->id === T_STRING;
    }

    /**
     * Get namespace name active at specified token position.
     */
    private function activeNamespace(int $tokenID): string
    {
        foreach ($this->namespaces as $namespace => $position) {
            if ($tokenID >= $position[self::O_TOKEN] && $tokenID <= $position[self::C_TOKEN]) {
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
     * Find token ID of ending brace.
     */
    private function endingToken(int $tokenID): int
    {
        $level = null;
        for ($localID = $tokenID; $localID < $this->countTokens; ++$localID) {
            $token = $this->tokens[$localID];
            if ($token->text === '{') {
                ++$level;
                continue;
            }

            if ($token->text === '}') {
                --$level;
            }

            if ($level === 0) {
                break;
            }
        }

        return $localID;
    }
}
