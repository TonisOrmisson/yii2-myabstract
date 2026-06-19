<?php


namespace andmemasin\myabstract;


use andmemasin\myabstract\interfaces\MultiThreadableInterface;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;
use yii\db\Connection;
use yii\db\Expression;

/**
 * @implements MultiThreadableInterface<static>
 */
class MyThreadableRecord extends MyActiveRecord implements MultiThreadableInterface
{
    protected string $processColumnName = 'processing_by';
    protected string $jobColumnName = 'processing_job_id';

    /**
     * @param string $jobId
     * @param string $processId
     * @param int $limit
     * @param array<int, mixed> $conditions
     * @return integer count of rows updated
     * @throws \yii\db\Exception
     */
    public function setJob(string $jobId, string $processId, int $limit = 0, array $conditions = []): int
    {

        $db = $this->dbConnection();

        $setValues = [
            $this->processColumnName => $processId,
            $this->jobColumnName => $jobId,
        ];

        $query = $db->createCommand()
            ->update(static::tableName(), $setValues, $conditions);
        $sql = $query->rawSql . " " . new Expression(" LIMIT $limit");
        $cache = $this->getCache();
        if($cache !== null) {
            TagDependency::invalidate($cache, static::cahceDepencencyTagTable());
        }


        return $db->createCommand($sql)->execute();
    }


    /**
     * @param string $jobId
     * @return integer count of rows updated
     */
    public function clearJob(string $jobId): int
    {
        $db = $this->dbConnection();
        $conditions = [
            $this->jobColumnName => $jobId,
        ];
        $setValues = [
            $this->processColumnName => new Expression('NULL'),
            $this->jobColumnName => new Expression('NULL'),
        ];

        $query = $db->createCommand()
            ->update(static::tableName(), $setValues, $conditions);
        return $query->execute();
    }

    /**
     * Find all records being locked for this job
     * @param string $jobId
     * @return ActiveQuery<static>
     */
    public function findJobRows(string $jobId): ActiveQuery
    {
        return static::find()->andWhere([$this->jobColumnName => $jobId]);
    }

    /**
     * Find all records being locked for this process
     * @param string $processId
     * @return ActiveQuery<static>
     */
    public function findProcessRows(string $processId): ActiveQuery
    {
        return static::find()->andWhere([$this->processColumnName => $processId]);
    }

    /**
     * Clear all records of any processing status
     * @return int
     */
    public function clearAllProcesses(): int
    {
        $db = $this->dbConnection();
        $setValues = [
            $this->processColumnName => new Expression('NULL'),
            $this->jobColumnName => new Expression('NULL'),
        ];

        $query = $db->createCommand()
            ->update(static::tableName(), $setValues);
        return $query->execute();
    }

    private function dbConnection(): Connection
    {
        $app = \Yii::$app;
        if ($app === null) {
            throw new InvalidConfigException('Yii application is not configured');
        }

        $db = $app->get('db');
        if (!$db instanceof Connection) {
            throw new InvalidConfigException('Yii db component must be an instance of ' . Connection::class);
        }

        return $db;
    }
}
