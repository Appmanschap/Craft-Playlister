<?php

namespace appmanschap\craftplaylister\traits;

use Craft;
use craft\db\Query;
use craft\db\Table;
use craft\queue\BaseJob;
use craft\queue\Queue;

/**
 * @link      https://www.appmanschap.nl
 * @copyright Copyright (c) 2024 Appmanschap
 */
trait HasJobs
{
    /**
     * @return string
     */
    abstract public function getUniqueJobPayload(): string;

    /**
     * @param class-string $jobClass
     * @return Query<array-key, mixed>
     */
    public function getJobQuery(string $jobClass): Query
    {
        return (new Query())->from(Table::QUEUE)
            ->where(['like', 'job', $jobClass])
            ->andWhere(['like', 'job', $this->getUniqueJobPayload()]);
    }

    /**
     * @param class-string $jobClass
     * @return bool
     */
    public function hasRunningJob(string $jobClass): bool
    {
        return $this->getJobQuery($jobClass)->exists();
    }

    /**
     * @param BaseJob $job
     * @return void
     */
    public function pushJob(BaseJob $job): void
    {
        Craft::$app->getQueue()->push($job);
    }

    /**
     * @param class-string $jobClass
     * @return void
     */
    public function releaseJobs(string $jobClass): void
    {
        $this->getJobQuery($jobClass)
            ->collect()
            ->each(function($job) {
                if (!is_array($job) || !isset($job['id'])) {
                    return;
                }

                /** @var Queue $queue */
                $queue = Craft::$app->getQueue();
                $queue->release($job['id']);
            });
    }
}
