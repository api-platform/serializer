<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ApiPlatform\Core\Serializer\Filter;

use Symfony\Component\HttpFoundation\Request;

/**
 * Group filter.
 *
 * @author Baptiste Meyer <baptiste.meyer@gmail.com>
 */
final class GroupFilter implements FilterInterface
{
    private $overrideDefaultGroups;
    private $parameterName;
    private $whitelist;

    public function __construct(string $parameterName = 'groups', bool $overrideDefaultGroups = false, array $whitelist = null)
    {
        $this->overrideDefaultGroups = $overrideDefaultGroups;
        $this->parameterName = $parameterName;
        $this->whitelist = $whitelist;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, bool $normalization, array $attributes, array &$context)
    {
        if (array_key_exists($this->parameterName, $commonAttribute = $request->attributes->get('_api_filter_common', []))) {
            $groups = $commonAttribute[$this->parameterName];
        } else {
            $groups = $request->query->get($this->parameterName);
        }

        if (!is_array($groups)) {
            return;
        }

        if (null !== $this->whitelist) {
            $groups = array_intersect($this->whitelist, $groups);
        }

        if (!$this->overrideDefaultGroups && isset($context['groups'])) {
            $groups = array_merge((array) $context['groups'], $groups);
        }

        $context['groups'] = $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(string $resourceClass): array
    {
        return [
            $this->parameterName.'[]' => [
                'property' => null,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
