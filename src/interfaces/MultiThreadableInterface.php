<?php


namespace andmemasin\myabstract\interfaces;

use yii\db\ActiveQuery;

/**
 * Interface MultiThreadableTrait
 * @property string $processing_by An identifier to lock a row as "currently being used by" some queue thread to avoid from using by another thread
 * @property string $processing_job_id An identifier for one specific job
 * @package andmemasin\myabstract\traits
 */
interface MultiThreadableInterface
{

    /**
     * @param string $jobId
     * @param string $processId
     * @param int $limit
     * @param array<int, mixed> $conditions
     * @return integer count of rows updated
     */
    public function setJob(string $jobId, string $processId, int $limit = 0, array $conditions = []) : int ;

    /**
     * @param string $jobId
     * @return integer count of rows updated
     */
    public function clearJob(string $jobId) : int ;


    /**
     * Find all records being locked for this job
     * @param string $jobId
     * @return ActiveQuery
     */
    public function findJobRows(string $jobId): ActiveQuery;

    /**
     * Find all records being locked for this process
     * @param string $processId
     * @return ActiveQuery
     */
    public function findProcessRows(string $processId): ActiveQuery;

    /**
     * Clear all records of any processing status
     * @return int
     */
    public function clearAllProcesses() : int ;


}
