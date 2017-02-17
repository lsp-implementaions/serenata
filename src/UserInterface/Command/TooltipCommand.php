<?php

namespace PhpIntegrator\UserInterface\Command;

use ArrayAccess;

use PhpIntegrator\Tooltips\TooltipProvider;

use PhpIntegrator\Utility\SourceCodeHelpers;
use PhpIntegrator\Utility\SourceCodeStreamReader;

/**
 * Command that fetches tooltip information for a specific location.
 */
class TooltipCommand extends AbstractCommand
{
    /**
     * @var TooltipProvider
     */
    protected $tooltipProvider;

    /**
     * @var SourceCodeStreamReader
     */
    protected $sourceCodeStreamReader;

    /**
     * @param TooltipProvider        $tooltipProvider
     * @param SourceCodeStreamReader $sourceCodeStreamReader
     */
    public function __construct(TooltipProvider $tooltipProvider, SourceCodeStreamReader $sourceCodeStreamReader)
    {
        $this->tooltipProvider = $tooltipProvider;
        $this->sourceCodeStreamReader = $sourceCodeStreamReader;
    }

    /**
     * @inheritDoc
     */
    public function execute(ArrayAccess $arguments)
    {
        if (!isset($arguments['offset'])) {
            throw new InvalidArgumentsException('An --offset must be supplied into the source code!');
        }

        $code = null;

        if (isset($arguments['stdin']) && $arguments['stdin']) {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromStdin();
        } elseif (isset($arguments['file']) && $arguments['file']) {
            $code = $this->sourceCodeStreamReader->getSourceCodeFromFile($arguments['file']);
        } else {
            throw new InvalidArgumentsException('Either a --file file must be supplied or --stdin must be passed!');
        }

        $offset = $arguments['offset'];

        if (isset($arguments['charoffset']) && $arguments['charoffset'] == true) {
            $offset = SourceCodeHelpers::getByteOffsetFromCharacterOffset($offset, $code);
        }

        $result = $this->tooltipProvider->get($code, $offset);

        return $result;
    }
}