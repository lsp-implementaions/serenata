<?php

namespace PhpIntegrator\Analysis\Conversion;

use PhpIntegrator\Indexing\Structures;

/**
 * Converts raw classlike data from the index to more useful data.
 */
class ClasslikeConverter extends AbstractConverter
{
    /**
     * @param Structures\Classlike $classlike
     *
     * @return array
     */
    public function convert(Structures\Classlike $classlike): array
    {
        $data = [
            'name'               => $classlike->getName(),
            'fqcn'               => $classlike->getFqcn(),
            'startLine'          => $classlike->getStartLine(),
            'endLine'            => $classlike->getEndLine(),
            'filename'           => $classlike->getFile()->getPath(),
            'type'               => $classlike->getTypeName(),
            'isDeprecated'       => $classlike->getIsDeprecated(),
            'hasDocblock'        => $classlike->getHasDocblock(),
            'hasDocumentation'   => $classlike->getHasDocblock(),
            'shortDescription'   => $classlike->getShortDescription(),
            'longDescription'    => $classlike->getLongDescription()
        ];

        if ($classlike instanceof Structures\Class_) {
            $data['isAnonymous']  = $classlike->getIsAnonymous();
            $data['isAbstract']   = $classlike->getIsAbstract();
            $data['isFinal']      = $classlike->getIsFinal();
            $data['isAnnotation'] = $classlike->getIsAnnotation();
        }

        return $data;
    }
}
