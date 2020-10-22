<?php

namespace Quantum\Doctrine;

use Quantum\Doctrine;
use Quantum\Uuid;

/** @MappedSuperclass
 *  @HasLifecycleCallbacks
 */
abstract class Entity
{

    /**
     * @Id
     * @GeneratedValue
     * @Column(type="bigint")
     */
    private $id;

    /** @Column(name="created_at", type="string") */
    private $created_at;

    /** @Column(name="updated_at", type="string") */
    private $updated_at;

    /** @Column(name="uuid", type="string") */
    private $uuid;

    /** @PrePersist */
    public function _create_timestamps()
    {
        $datetime = date('Y-m-d H:i:s');

        $this->uuid = Uuid::v4();
        $this->created_at = $datetime;
        $this->updated_at = $datetime;
    }

    /** @PreUpdate */
    public function _update_timestamps()
    {
        $this->updated_at = date('Y-m-d H:i:s');
    }


    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete()
    {
        $manager = Doctrine::getEntityManager();

        $manager->remove($this);
        $manager->flush();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save()
    {
        $manager = Doctrine::getEntityManager();

        $manager->persist($this);
        $manager->flush();
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     */
    public function persist()
    {
        $manager = Doctrine::getEntityManager();
        $manager->persist($this);
    }

    /**
     * @param $criteria
     * @param null $orderBy
     * @param null $limit
     * @param null $offset
     * @return array|object[]
     */
    public static function findBy($criteria, $orderBy = null, $limit = null, $offset = null)
    {
        return self::getRepository()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param $criteria
     * @return object|null
     */
    public static function findOneBy($criteria)
    {
        return self::getRepository()->findOneBy($criteria);
    }

    /**
     * @param $id
     * @return object|null
     */
    public static function findById($id)
    {
        return self::getRepository()->find($id);
    }

    /**
     * @param $uuid
     * @return object|null
     */
    public static function findByUuid($uuid)
    {
        return self::getRepository()->findOneBy(['uuid' => $uuid]);
    }

    /**
     * @return array
     */
    public static function findAll()
    {
        return self::getRepository()->findAll();
    }

    /**
     * @return mixed
     */
    public static function createBlank()
    {
        $class_name = get_called_class();

        return new $class_name;
    }


    /**
     * @return \Doctrine\ORM\EntityRepository|\Doctrine\Persistence\ObjectRepository
     */
    public static function getRepository()
    {
        $class_name = get_called_class();

        $manager = Doctrine::getEntityManager();

        return $manager->getRepository($class_name);
    }




}