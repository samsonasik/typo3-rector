<?php

declare(strict_types=1);

namespace Ssch\TYPO3Rector\Rector\v10\v0;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use TYPO3\CMS\Form\Domain\Finishers\EmailFinisher;

/**
 * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Deprecation-87200-EmailFinisherFormatContants.html
 */
final class RemoveFormatConstantsEmailFinisherRector extends AbstractRector
{
    /**
     * @var string
     */
    private const FORMAT_HTML = 'FORMAT_HTML';

    /**
     * @var string
     */
    private const FORMAT = 'format';

    /**
     * @var string
     */
    private const ADD_HTML_PART = 'addHtmlPart';

    public function getNodeTypes(): array
    {
        return [ClassConstFetch::class];
    }

    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node, EmailFinisher::class)) {
            return null;
        }
        if (! $this->isNames($node->name, [self::FORMAT_HTML, 'FORMAT_PLAINTEXT'])) {
            return null;
        }
        $parent = $node->getAttribute('parent');
        if ($parent instanceof Arg) {
            return $this->refactorSetOptionMethodCall($parent, $node);
        }
        if ($parent instanceof ArrayItem) {
            return $this->refactorArrayItemOption($parent, $node);
        }
        if ($parent instanceof Assign) {
            return $this->refactorOptionAssignment($parent, $node);
        }
        if ($parent instanceof Identical) {
            return $this->refactorCondition($parent, $node);
        }
        return null;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove constants FORMAT_PLAINTEXT and FORMAT_HTML of class TYPO3\CMS\Form\Domain\Finishers\EmailFinisher',
            [
                new CodeSample(<<<'PHP'
$this->setOption(self::FORMAT, EmailFinisher::FORMAT_HTML);
PHP
                    , <<<'PHP'
$this->setOption('addHtmlPart', true);
PHP
                ),
            ]
        );
    }

    private function refactorSetOptionMethodCall(Arg $parent, ClassConstFetch $node): ?Node
    {
        $parent = $parent->getAttribute('parent');
        if (! $parent instanceof MethodCall) {
            return null;
        }
        if (! $this->isName($parent->name, 'setOption')) {
            return null;
        }
        if (! $this->valueResolver->isValue($parent->args[0]->value, self::FORMAT)) {
            return null;
        }
        $addHtmlPart = $this->isName($node->name, self::FORMAT_HTML);
        $parent->args[0]->value = new String_(self::ADD_HTML_PART);
        $parent->args[1]->value = $addHtmlPart ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
        return $node;
    }

    private function refactorArrayItemOption(ArrayItem $parent, ClassConstFetch $node): ?Node
    {
        if (null === $parent->key || ! $this->valueResolver->isValue($parent->key, self::FORMAT)) {
            return null;
        }
        $addHtmlPart = $this->isName($node->name, self::FORMAT_HTML);
        $parent->key = new String_(self::ADD_HTML_PART);
        $parent->value = $addHtmlPart ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
        return $node;
    }

    private function refactorOptionAssignment(Assign $parent, ClassConstFetch $node): ?Node
    {
        if (! $parent->var instanceof ArrayDimFetch) {
            return null;
        }
        if (! $this->isName($parent->var->var, 'options')) {
            return null;
        }
        if (null === $parent->var->dim || ! $this->valueResolver->isValue($parent->var->dim, self::FORMAT)) {
            return null;
        }
        $addHtmlPart = $this->isName($node->name, self::FORMAT_HTML);
        $parent->var->dim = new String_(self::ADD_HTML_PART);
        $parent->expr = $addHtmlPart ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
        return $node;
    }

    private function refactorCondition(Identical $parent, ClassConstFetch $node): ?Node
    {
        if (! $parent->left instanceof ArrayDimFetch) {
            return null;
        }
        if (! $this->isName($parent->left->var, 'options')) {
            return null;
        }
        if (null === $parent->left->dim || ! $this->valueResolver->isValue($parent->left->dim, self::FORMAT)) {
            return null;
        }
        $addHtmlPart = $this->isName($node->name, self::FORMAT_HTML);
        $parent->left->dim = new String_(self::ADD_HTML_PART);
        $parent->right = $addHtmlPart ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
        return $node;
    }
}
