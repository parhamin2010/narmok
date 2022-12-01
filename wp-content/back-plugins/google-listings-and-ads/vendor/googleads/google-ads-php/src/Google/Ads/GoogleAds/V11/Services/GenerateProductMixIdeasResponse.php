<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v11/services/reach_plan_service.proto

namespace Google\Ads\GoogleAds\V11\Services;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * The suggested product mix.
 *
 * Generated from protobuf message <code>google.ads.googleads.v11.services.GenerateProductMixIdeasResponse</code>
 */
class GenerateProductMixIdeasResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * A list of products (ad formats) and the associated budget allocation idea.
     *
     * Generated from protobuf field <code>repeated .google.ads.googleads.v11.services.ProductAllocation product_allocation = 1;</code>
     */
    private $product_allocation;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type \Google\Ads\GoogleAds\V11\Services\ProductAllocation[]|\Google\Protobuf\Internal\RepeatedField $product_allocation
     *           A list of products (ad formats) and the associated budget allocation idea.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Ads\GoogleAds\V11\Services\ReachPlanService::initOnce();
        parent::__construct($data);
    }

    /**
     * A list of products (ad formats) and the associated budget allocation idea.
     *
     * Generated from protobuf field <code>repeated .google.ads.googleads.v11.services.ProductAllocation product_allocation = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getProductAllocation()
    {
        return $this->product_allocation;
    }

    /**
     * A list of products (ad formats) and the associated budget allocation idea.
     *
     * Generated from protobuf field <code>repeated .google.ads.googleads.v11.services.ProductAllocation product_allocation = 1;</code>
     * @param \Google\Ads\GoogleAds\V11\Services\ProductAllocation[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setProductAllocation($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Google\Ads\GoogleAds\V11\Services\ProductAllocation::class);
        $this->product_allocation = $arr;

        return $this;
    }

}
