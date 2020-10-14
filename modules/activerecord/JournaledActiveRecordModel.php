<?php


namespace Quantum;

use ObjectJournal;

/**
 * Trait JournaledModelTrait
 * @package Quantum
 */
trait JournaledModelTrait
{
    /**
     * @return ObjectJournal
     */
    public function getJournal()
    {
        if (!empty($this->journal_id))
        {
            return ObjectJournal::find_by_id($this->journal_id);
        }

        $journal = new ObjectJournal();
        $journal->setParentKey(get_class($this).'_'.$this->id);
        $journal->setObjectClassName(get_class($this));
        $journal->setObjectId($this->id);
        $journal->type = "JournaledActiveRecordModel\\Auto";
        $journal->save();

        $this->journal_id = $journal->id;
        $this->save();

        return $journal;
    }

    /**
     * @return mixed
     */
    public function getJournalEntries()
    {
        $journal = $this->getJournal();

        return $journal->getEntries();
    }

    /**
     * @param $data
     * @param string $key
     * @param string $type
     */
    public function addJournalEntry($data, $type = "", $key = "")
    {
        $this->getJournal()->addEntry($data, $type, $key);
    }


    /**
     * @param $message
     */
    public function addToJournal($message)
    {
        $this->addJournalEntry($message, 'info', 'info');
    }

    /**
     * @param $message
     */
    public function addErrorToJournal($message)
    {
        $this->addJournalEntry($message, 'error', 'error');
    }

    /**
     * @param $message
     */
    public function addSuccessToJournal($message)
    {
        $this->addJournalEntry($message, 'success', 'success');
    }
}


/**
 * Class JournaledActiveRecordModel
 * Requires a journal_id column
 * @package Quantum
 */
class JournaledActiveRecordModel extends ActiverecordModel
{
    use JournaledModelTrait;

}