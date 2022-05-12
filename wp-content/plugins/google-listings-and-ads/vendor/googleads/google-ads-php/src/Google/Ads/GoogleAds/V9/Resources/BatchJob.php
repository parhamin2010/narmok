<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v9/resources/batch_job.proto

namespace Google\Ads\GoogleAds\V9\Resources;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * A list of mutates being processed asynchronously. The mutates are uploaded
 * by the user. The mutates themselves aren't readable and the results of the
 * job can only be read using BatchJobService.ListBatchJobResults.
 *
 * Generated from protobuf message <code>google.ads.googleads.v9.resources.BatchJob</code>
 */
class BatchJob extends \Google\Protobuf\Internal\Message
{
    /**
     * Immutable. The resource name of the batch job.
     * Batch job resource names have the form:
     * `customers/{customer_id}/batchJobs/{batch_job_id}`
     *
     * Generated from protobuf field <code>string resource_name = 1 [(.google.api.field_behavior) = IMMUTABLE, (.google.api.resource_reference) = {</code>
     */
    protected $resource_name = '';
    /**
     * Output only. ID of this batch job.
     *
     * Generated from protobuf field <code>optional int64 id = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    protected $id = null;
    /**
     * Output only. The next sequence token to use when adding operations. Only set when the
     * batch job status is PENDING.
     *
     * Generated from protobuf field <code>optional string next_add_sequence_token = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    protected $next_add_sequence_token = null;
    /**
     * Output only. Contains additional information about this batch job.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v9.resources.BatchJob.BatchJobMetadata metadata = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    protected $metadata = null;
    /**
     * Output only. Status of this batch job.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v9.enums.BatchJobStatusEnum.BatchJobStatus status = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    protected $status = 0;
    /**
     * Output only. The resource name of the long-running operation that can be used to poll
     * for completion. Only set when the batch job status is RUNNING or DONE.
     *
     * Generated from protobuf field <code>optional string long_running_operation = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     */
    protected $long_running_operation = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $resource_name
     *           Immutable. The resource name of the batch job.
     *           Batch job resource names have the form:
     *           `customers/{customer_id}/batchJobs/{batch_job_id}`
     *     @type int|string $id
     *           Output only. ID of this batch job.
     *     @type string $next_add_sequence_token
     *           Output only. The next sequence token to use when adding operations. Only set when the
     *           batch job status is PENDING.
     *     @type \Google\Ads\GoogleAds\V9\Resources\BatchJob\BatchJobMetadata $metadata
     *           Output only. Contains additional information about this batch job.
     *     @type int $status
     *           Output only. Status of this batch job.
     *     @type string $long_running_operation
     *           Output only. The resource name of the long-running operation that can be used to poll
     *           for completion. Only set when the batch job status is RUNNING or DONE.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Ads\GoogleAds\V9\Resources\BatchJob::initOnce();
        parent::__construct($data);
    }

    /**
     * Immutable. The resource name of the batch job.
     * Batch job resource names have the form:
     * `customers/{customer_id}/batchJobs/{batch_job_id}`
     *
     * Generated from protobuf field <code>string resource_name = 1 [(.google.api.field_behavior) = IMMUTABLE, (.google.api.resource_reference) = {</code>
     * @return string
     */
    public function getResourceName()
    {
        return $this->resource_name;
    }

    /**
     * Immutable. The resource name of the batch job.
     * Batch job resource names have the form:
     * `customers/{customer_id}/batchJobs/{batch_job_id}`
     *
     * Generated from protobuf field <code>string resource_name = 1 [(.google.api.field_behavior) = IMMUTABLE, (.google.api.resource_reference) = {</code>
     * @param string $var
     * @return $this
     */
    public function setResourceName($var)
    {
        GPBUtil::checkString($var, True);
        $this->resource_name = $var;

        return $this;
    }

    /**
     * Output only. ID of this batch job.
     *
     * Generated from protobuf field <code>optional int64 id = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return int|string
     */
    public function getId()
    {
        return isset($this->id) ? $this->id : 0;
    }

    public function hasId()
    {
        return isset($this->id);
    }

    public function clearId()
    {
        unset($this->id);
    }

    /**
     * Output only. ID of this batch job.
     *
     * Generated from protobuf field <code>optional int64 id = 7 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param int|string $var
     * @return $this
     */
    public function setId($var)
    {
        GPBUtil::checkInt64($var);
        $this->id = $var;

        return $this;
    }

    /**
     * Output only. The next sequence token to use when adding operations. Only set when the
     * batch job status is PENDING.
     *
     * Generated from protobuf field <code>optional string next_add_sequence_token = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getNextAddSequenceToken()
    {
        return isset($this->next_add_sequence_token) ? $this->next_add_sequence_token : '';
    }

    public function hasNextAddSequenceToken()
    {
        return isset($this->next_add_sequence_token);
    }

    public function clearNextAddSequenceToken()
    {
        unset($this->next_add_sequence_token);
    }

    /**
     * Output only. The next sequence token to use when adding operations. Only set when the
     * batch job status is PENDING.
     *
     * Generated from protobuf field <code>optional string next_add_sequence_token = 8 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string $var
     * @return $this
     */
    public function setNextAddSequenceToken($var)
    {
        GPBUtil::checkString($var, True);
        $this->next_add_sequence_token = $var;

        return $this;
    }

    /**
     * Output only. Contains additional information about this batch job.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v9.resources.BatchJob.BatchJobMetadata metadata = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return \Google\Ads\GoogleAds\V9\Resources\BatchJob\BatchJobMetadata|null
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    public function hasMetadata()
    {
        return isset($this->metadata);
    }

    public function clearMetadata()
    {
        unset($this->metadata);
    }

    /**
     * Output only. Contains additional information about this batch job.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v9.resources.BatchJob.BatchJobMetadata metadata = 4 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param \Google\Ads\GoogleAds\V9\Resources\BatchJob\BatchJobMetadata $var
     * @return $this
     */
    public function setMetadata($var)
    {
        GPBUtil::checkMessage($var, \Google\Ads\GoogleAds\V9\Resources\BatchJob\BatchJobMetadata::class);
        $this->metadata = $var;

        return $this;
    }

    /**
     * Output only. Status of this batch job.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v9.enums.BatchJobStatusEnum.BatchJobStatus status = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Output only. Status of this batch job.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v9.enums.BatchJobStatusEnum.BatchJobStatus status = 5 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param int $var
     * @return $this
     */
    public function setStatus($var)
    {
        GPBUtil::checkEnum($var, \Google\Ads\GoogleAds\V9\Enums\BatchJobStatusEnum\BatchJobStatus::class);
        $this->status = $var;

        return $this;
    }

    /**
     * Output only. The resource name of the long-running operation that can be used to poll
     * for completion. Only set when the batch job status is RUNNING or DONE.
     *
     * Generated from protobuf field <code>optional string long_running_operation = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @return string
     */
    public function getLongRunningOperation()
    {
        return isset($this->long_running_operation) ? $this->long_running_operation : '';
    }

    public function hasLongRunningOperation()
    {
        return isset($this->long_running_operation);
    }

    public function clearLongRunningOperation()
    {
        unset($this->long_running_operation);
    }

    /**
     * Output only. The resource name of the long-running operation that can be used to poll
     * for completion. Only set when the batch job status is RUNNING or DONE.
     *
     * Generated from protobuf field <code>optional string long_running_operation = 9 [(.google.api.field_behavior) = OUTPUT_ONLY];</code>
     * @param string $var
     * @return $this
     */
    public function setLongRunningOperation($var)
    {
        GPBUtil::checkString($var, True);
        $this->long_running_operation = $var;

        return $this;
    }

}

