<?php
/**
 * This file is part of the JSON Object Mapper package.
 *
 * Copyright 2017 - 2018 by Julian Finkler <julian@mintware.de>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace MintWare\JOM;

/**
 * This class represents the JsonField Annotation
 *
 * @Annotation
 */
class JsonField
{
    /**
     * The name of the field in the JSON object
     *
     * @var string
     */
    public $name = null;

    /**
     * The type of the field
     *
     * @var string
     */
    public $type = null;

    /**
     * Transformer class
     *
     * If this transformer is set, the type handler is ignored
     * and the raw data will be passed to the transformer to
     * handle the data.
     *
     * @var string
     */
    public $transformer = null;

    /**
     * Pre transformer class
     *
     * This transformer transforms the data BEFORE the
     * type handler handles the data.
     *
     * @var string
     */
    public $preTransformer = null;

    /**
     * Post transformer class
     *
     * This transformer transforms the data AFTER the
     * type handler has handled the data.
     *
     * @var string
     */
    public $postTransformer = null;
}
