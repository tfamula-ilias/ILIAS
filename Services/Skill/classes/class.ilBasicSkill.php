<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Basic Skill
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilBasicSkill extends ilSkillTreeNode implements ilSkillUsageInfo
{
    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilBasicSkillLevelRepository
     */
    protected $bsc_skl_lvl_db_rep;

    /**
     * @var ilBasicSkillUserLevelRepository
     */
    protected $bsc_skl_usr_lvl_db_rep;

    /**
     * @var ilBasicSkillTreeRepository
     */
    protected $bsc_skl_tre_rep;

    //TODO: What to do with these constants?
    const ACHIEVED = 1;
    const NOT_ACHIEVED = 0;

    const EVAL_BY_OTHERS_ = 0;
    const EVAL_BY_SELF = 1;
    const EVAL_BY_ALL = 2;

    public $id;

    /**
     * ilBasicSkill constructor.
     * @param int                                  $a_id
     * @param ilBasicSkillLevelRepository|null     $bsc_skl_lvl_db_rep
     * @param ilBasicSkillUserLevelRepository|null $bsc_skl_usr_lvl_db_rep
     * @param ilBasicSkillTreeRepository|null      $bsc_skl_tre_rep
     */
    public function __construct(
        int $a_id = 0,
        ilBasicSkillLevelRepository $bsc_skl_lvl_db_rep = null,
        ilBasicSkillUserLevelRepository $bsc_skl_usr_lvl_db_rep = null,
        ilBasicSkillTreeRepository $bsc_skl_tre_rep = null
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();

        if (is_null($bsc_skl_lvl_db_rep)) {
            $this->bsc_skl_lvl_db_rep = $DIC->skills()->internal()->repo()->getLevelRepo();
        }
        else {
            $this->bsc_skl_lvl_db_rep = $bsc_skl_lvl_db_rep;
        }

        if (is_null($bsc_skl_usr_lvl_db_rep)) {
            $this->bsc_skl_usr_lvl_db_rep = $DIC->skills()->internal()->repo()->getUserLevelRepo();
        }
        else {
            $this->bsc_skl_usr_lvl_db_rep = $bsc_skl_usr_lvl_db_rep;
        }

        if (is_null($bsc_skl_tre_rep)) {
            $this->bsc_skl_tre_rep = $DIC->skills()->internal()->repo()->getTreeRepo();
        }
        else {
            $this->bsc_skl_tre_rep = $bsc_skl_tre_rep;
        }

        parent::__construct($a_id);
        $this->setType("skll");
    }

    /**
     * Read data from database
     */
    public function read()
    {
        parent::read();
    }

    /**
     * Create skill
     */
    public function create()
    {
        parent::create();
    }

    /**
     * Delete skill
     */
    public function delete()
    {
        $skill_id = $this->getId();
        $this->bsc_skl_lvl_db_rep->deleteLevelsOfSkill($skill_id);
        $this->bsc_skl_usr_lvl_db_rep->deleteUserLevelsOfSkill($skill_id);

        parent::delete();
    }

    /**
     * Copy basic skill
     */
    public function copy()
    {
        $skill = new ilBasicSkill();
        $skill->setTitle($this->getTitle());
        $skill->setDescription($this->getDescription());
        $skill->setType($this->getType());
        $skill->setSelfEvaluation($this->getSelfEvaluation());
        $skill->setOrderNr($this->getOrderNr());
        $skill->create();

        $levels = $this->getLevelData();
        if (sizeof($levels)) {
            foreach ($levels as $item) {
                $skill->addLevel($item["title"], $item["description"]);
            }
        }
        $skill->update();

        return $skill;
    }

    //
    //
    // Skill level related methods
    //
    //

    public function addLevel(string $a_title, string $a_description, string $a_import_id = "")
    {
        $skill_id = $this->getId();
        $this->bsc_skl_lvl_db_rep->addLevel($skill_id, $a_title, $a_description, $a_import_id);
    }

    public function getLevelData(int $a_id = 0) : array
    {
        $skill_id = $this->getId();

        return $this->bsc_skl_lvl_db_rep->getLevelData($skill_id, $a_id);
    }

    public static function lookupLevelTitle(int $a_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $repository = new ilBasicSkillLevelDBRepository($ilDB);

        return $repository->lookupLevelTitle($a_id);
    }

    public static function lookupLevelDescription(int $a_id) : string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $repository = new ilBasicSkillLevelDBRepository($ilDB);

        return $repository->lookupLevelDescription($a_id);
    }

    public static function lookupLevelSkillId(int $a_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $repository = new ilBasicSkillLevelDBRepository($ilDB);

        return $repository->lookupLevelSkillId($a_id);
    }

    public static function writeLevelTitle(int $a_id, string $a_title)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $repository = new ilBasicSkillLevelDBRepository($ilDB);
        $repository->writeLevelTitle($a_id, $a_title);
    }

    public static function writeLevelDescription(int $a_id, string $a_description)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $repository = new ilBasicSkillLevelDBRepository($ilDB);
        $repository->writeLevelDescription($a_id, $a_description);
    }

    public function updateLevelOrder(array $order)
    {
        asort($order);
        $this->bsc_skl_lvl_db_rep->updateLevelOrder($order);
    }

    public function deleteLevel(int $a_id)
    {
        $this->bsc_skl_lvl_db_rep->deleteLevel($a_id);
    }

    public function fixLevelNumbering()
    {
        $skill_id = $this->getId();
        $this->bsc_skl_lvl_db_rep->fixLevelNumbering($skill_id);
    }

    public function getSkillForLevelId(int $a_level_id)
    {
        return $this->bsc_skl_lvl_db_rep->getSkillForLevelId($a_level_id);
    }

    //
    //
    // User skill (level) related methods
    //
    //

    public static function resetUserSkillLevelStatus(
        int $a_user_id,
        int $a_skill_id,
        int $a_tref_id = 0,
        int $a_trigger_ref_id = 0,
        bool $a_self_eval = false
    ) {
        global $DIC;

        $ilDB = $DIC->database();

        if (!$a_self_eval) {
            throw new ilSkillException("resetUserSkillLevelStatus currently only provided for self evaluations.");
        }

        $obj_adapter = new ilSkillObjectAdapter();
        $trigger_obj_id = ($a_trigger_ref_id > 0)
            ? $obj_adapter->getObjIdForRefId($a_trigger_ref_id)
            : 0;

        $update = false;
        $repository = new ilBasicSkillUserLevelDBRepository($ilDB);
        $status_date = $repository->hasRecentSelfEvaluation($a_user_id, $a_skill_id, $a_tref_id, $a_trigger_ref_id);
        if ($status_date != "") {
            $update = true;
        }

        $repository->resetUserSkillLevelStatus($update, $trigger_obj_id, $status_date, $a_user_id, $a_skill_id,
            $a_tref_id, $a_trigger_ref_id, $a_self_eval);
    }

    protected static function hasRecentSelfEvaluation(
        int $a_user_id,
        int $a_skill_id,
        int $a_tref_id = 0,
        int $a_trigger_ref_id = 0
    ) {
        global $DIC;

        $ilDB = $DIC->database();

        $obj_adapter = new ilSkillObjectAdapter();
        $trigger_obj_id = ($a_trigger_ref_id > 0)
            ? $obj_adapter->getObjIdForRefId($a_trigger_ref_id)
            : 0;
        $repository = new ilBasicSkillUserLevelDBRepository($ilDB);

        return $repository->hasRecentSelfEvaluation($trigger_obj_id, $a_user_id, $a_skill_id, $a_tref_id,
            $a_trigger_ref_id);
    }

    public static function getNewAchievementsPerUser(
        string $a_timestamp,
        string $a_timestamp_to = null,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $repository = new ilBasicSkillUserLevelDBRepository($ilDB);

        return $repository->getNewAchievementsPerUser($a_timestamp, $a_timestamp_to, $a_user_id, $a_self_eval);
    }

    public static function writeUserSkillLevelStatus(
        int $a_level_id,
        int $a_user_id,
        int $a_trigger_ref_id,
        int $a_tref_id = 0,
        int $a_status = ilBasicSkill::ACHIEVED,
        bool $a_force = false,
        bool $a_self_eval = false,
        string $a_unique_identifier = "",
        float $a_next_level_fulfilment = 0.0
    ) {
        global $DIC;

        $ilDB = $DIC->database();

        $skill_id = ilBasicSkill::lookupLevelSkillId($a_level_id);
        $trigger_ref_id = $a_trigger_ref_id;
        $obj_adapter = new ilSkillObjectAdapter();
        $trigger_obj_id = $obj_adapter->getObjIdForRefId($trigger_ref_id);
        $trigger_title = $obj_adapter->getTitleForObjId($trigger_obj_id);
        $trigger_type = $obj_adapter->getTypeForObjId($trigger_obj_id);

        $status_date = "";
        $update = false;

        // self evaluations will update, if the last self evaluation is on the same day
        if ($a_self_eval && self::hasRecentSelfEvaluation($a_user_id, $skill_id, $a_tref_id, $trigger_ref_id)) {
            $status_date = self::hasRecentSelfEvaluation($a_user_id, $skill_id, $a_tref_id, $trigger_ref_id);
            if ($status_date != "") {
                $update = true;
            }
        }

        //next level percentage fulfilment value must be >=0 and <1
        if (!($a_next_level_fulfilment >= 0) || !($a_next_level_fulfilment < 1)) {
            throw new \UnexpectedValueException(
                "Next level fulfilment must be equal to or greater than 0 and less than 1, '" .
                $a_next_level_fulfilment . "' given."
            );
        }

        $repository = new ilBasicSkillUserLevelDBRepository($ilDB);
        $repository->writeUserSkillLevelStatus($skill_id, $trigger_ref_id, $trigger_obj_id, $trigger_title,
            $trigger_type, $update, $status_date, $a_level_id, $a_user_id, $a_tref_id, $a_self_eval,
            $a_unique_identifier, $a_next_level_fulfilment);
    }

    public static function removeAllUserSkillLevelStatusOfObject(
        int $a_user_id,
        int $a_trigger_obj_id,
        bool $a_self_eval = false,
        string $a_unique_identifier = ""
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_trigger_obj_id == 0) {
            return false;
        }
        $repository = new ilBasicSkillUserLevelDBRepository($ilDB);

        return $repository->removeAllUserSkillLevelStatusOfObject($a_user_id, $a_trigger_obj_id, $a_self_eval,
            $a_unique_identifier);
    }

    public static function removeAllUserData(int $a_user_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $repository = new ilBasicSkillUserLevelDBRepository($ilDB);
        $repository->removeAllUserData($a_user_id);
    }

    public function getMaxLevelPerType(
        int $a_tref_id,
        string $a_type,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int {
        if ($a_user_id == 0) {
            $a_user_id = $this->user->getId();
        }
        $skill_id = $this->getId();
        $levels = $this->getLevelData();

        return $this->bsc_skl_usr_lvl_db_rep->getMaxLevelPerType($skill_id, $levels, $a_tref_id, $a_type, $a_user_id,
            $a_self_eval);
    }

    public function getAllLevelEntriesOfUser(
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : array {
        if ($a_user_id == 0) {
            $a_user_id = $this->user->getId();
        }
        $skill_id = $this->getId();

        return $this->bsc_skl_usr_lvl_db_rep->getAllLevelEntriesOfUser($skill_id, $a_tref_id, $a_user_id, $a_self_eval);
    }

    public function getAllHistoricLevelEntriesOfUser(
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_eval_by = 0
    ) : array {
        if ($a_user_id == 0) {
            $a_user_id = $this->user->getId();
        }
        $skill_id = $this->getId();

        return $this->bsc_skl_usr_lvl_db_rep->getAllHistoricLevelEntriesOfUser($skill_id, $a_tref_id, $a_user_id,
            $a_eval_by);
    }

    public function getMaxLevelPerObject(
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int {
        if ($a_user_id == 0) {
            $a_user_id = $this->user->getId();
        }
        $skill_id = $this->getId();
        $levels = $this->getLevelData();

        return $this->bsc_skl_usr_lvl_db_rep->getMaxLevelPerObject($skill_id, $levels, $a_tref_id, $a_object_id,
            $a_user_id, $a_self_eval);
    }

    public function getMaxLevel(
        int $a_tref_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) : int {
        if ($a_user_id == 0) {
            $a_user_id = $this->user->getId();
        }
        $skill_id = $this->getId();
        $levels = $this->getLevelData();

        return $this->bsc_skl_usr_lvl_db_rep->getMaxLevel($skill_id, $levels, $a_tref_id, $a_user_id, $a_self_eval);
    }

    public static function hasSelfEvaluated(
        int $a_user_id,
        int $a_skill_id,
        int $a_tref_id
    ) : bool {
        global $DIC;

        $ilDB = $DIC->database();

        $repository = new ilBasicSkillUserLevelDBRepository($ilDB);

        return $repository->hasSelfEvaluated($a_user_id, $a_skill_id, $a_tref_id);
    }

    public function getLastLevelPerObject(
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) {
        if ($a_user_id == 0) {
            $a_user_id = $this->user->getId();
        }
        $skill_id = $this->getId();

        return $this->bsc_skl_usr_lvl_db_rep->getLastLevelPerObject($skill_id, $a_tref_id, $a_object_id, $a_user_id,
            $a_self_eval);
    }

    public function getLastUpdatePerObject(
        int $a_tref_id,
        int $a_object_id,
        int $a_user_id = 0,
        int $a_self_eval = 0
    ) {
        if ($a_user_id == 0) {
            $a_user_id = $this->user->getId();
        }
        $skill_id = $this->getId();

        return $this->bsc_skl_usr_lvl_db_rep->getLastUpdatePerObject($skill_id, $a_tref_id, $a_object_id, $a_user_id,
            $a_self_eval);
    }

    //
    //
    // Certificate related methods
    //
    //

    /**
     * Get title for certificate
     * @return string
     */
    public function getTitleForCertificate()
    {
        return $this->getTitle();
    }

    /**
     * Get short title for certificate
     * @return string
     */
    public function getShortTitleForCertificate()
    {
        return "Skill";
    }

    /**
     * Checks whether a skill level has a certificate or not
     * @param int $a_skill_id       skill id
     * @param int $a_skill_level_id skill level id
     * @return bool
     */
    public static function _lookupCertificate(int $a_skill_id, int $a_skill_level_id)
    {
        $certificatefile = CLIENT_WEB_DIR . "/certificates/skill/" .
            ((int) $a_skill_id) . "/" . ((int) $a_skill_level_id) . "/certificate.xml";
        if (@file_exists($certificatefile)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get usage info
     * @param array $a_cskill_ids
     * @param array $a_usages
     */
    public static function getUsageInfo($a_cskill_ids, &$a_usages)
    {
        ilSkillUsage::getUsageInfoGeneric(
            $a_cskill_ids,
            $a_usages,
            ilSkillUsage::USER_ASSIGNED,
            "skl_user_skill_level",
            "user_id"
        );
    }

    public static function getCommonSkillIdForImportId(
        int $a_source_inst_id,
        int $a_skill_import_id,
        int $a_tref_import_id = 0
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $tree = new ilSkillTree();

        if ($a_source_inst_id == 0) {
            return array();
        }

        $repository = new ilBasicSkillTreeDBRepository($ilDB);
        return $repository->getCommonSkillIdForImportId($tree, $a_source_inst_id, $a_skill_import_id,
            $a_tref_import_id);
    }

    public static function getLevelIdForImportId(int $a_source_inst_id, int $a_level_import_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $repository = new ilBasicSkillTreeDBRepository($ilDB);

        return $repository->getLevelIdForImportId($a_source_inst_id, $a_level_import_id);
    }

    /**
     * Get level ids for import Ids matching common skills
     * @param int $a_source_inst_id
     * @param int $a_level_import_id
     * @param int $a_skill_import_id
     * @param int $a_tref_import_id
     * @return array
     */
    public static function getLevelIdForImportIdMatchSkill(
        int $a_source_inst_id,
        int $a_level_import_id,
        int $a_skill_import_id,
        int $a_tref_import_id = 0
    ) : array {
        $level_id_data = self::getLevelIdForImportId($a_source_inst_id, $a_level_import_id);
        $skill_data = self::getCommonSkillIdForImportId($a_source_inst_id, $a_skill_import_id, $a_tref_import_id);
        $matches = array();
        foreach ($level_id_data as $l) {
            reset($skill_data);
            foreach ($skill_data as $s) {
                if (ilBasicSkill::lookupLevelSkillId($l["level_id"]) == $s["skill_id"]) {
                    $matches[] = array(
                        "level_id" => $l["level_id"],
                        "creation_date" => $l["creation_date"],
                        "skill_id" => $s["skill_id"],
                        "tref_id" => $s["tref_id"]
                    );
                }
            }
        }
        return $matches;
    }
}
