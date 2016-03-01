<?php

namespace Xxam\DynmodBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * DynmodRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DynmodRepository extends EntityRepository
{
    public function getTotalcount($filters){
        $query = $this->createQueryBuilder('e');
        $query->select('COUNT(DISTINCT e.id)');
        $z=0;
        foreach($filters as $key => $value){
            $query->andWhere('(e.'.$key.' = :value'.$z);
            $query->setParameter('value'.$z, $value);
        }
        return $query->getQuery()->getSingleScalarResult();
    }
    public function getModelFields(){
        $fields=Array();
        $fields[]=Array('name'=> 'id','type'=>'string','type'=>'int');
        $fields[]=Array('name'=> 'code','type'=>'string');
        $fields[]=Array('name'=> 'name','type'=>'string');
        $fields[]=Array('name'=> 'description','type'=>'string');
        $fields[]=Array('name'=> 'help','type'=>'string');
        $fields[]=Array('name'=> 'created','type'=>'date', 'dateFormat'=>'Y-m-d H.i.s');
        $fields[]=Array('name'=> 'updated','type'=>'date', 'dateFormat'=>'Y-m-d H.i.s');
        return $fields;

    }
    public function getGridColumns(){
        $columns=Array();
        $columns[]=Array('text'=> 'Id','dataIndex'=> 'id', 'filter'=> Array('type'=> 'number'),'hidden'=> true);
        $columns[]=Array('text'=> 'Code','dataIndex'=> 'code', 'filter'=> Array('type'=> 'string'));
        $columns[]=Array('text'=> 'Name','dataIndex'=> 'name', 'filter'=> Array('type'=> 'string'));
        $columns[]=Array('text'=> 'Description','flex'=> 1,'dataIndex'=> 'description', 'filter'=> Array('type'=> 'string'));
        $columns[]=Array('text'=> 'Help','flex'=> 1,'dataIndex'=> 'Help', 'filter'=> Array('type'=> 'string'));
        $columns[]=Array('text'=> 'Created','dataIndex'=> 'created', 'xtype'=> 'datecolumn', 'format'=>'Y-m-d H:i:s', 'filter'=> Array('type'=> 'date'),'hidden'=> true);
        $columns[]=Array('text'=> 'Updated','dataIndex'=> 'updated', 'xtype'=> 'datecolumn', 'format'=>'Y-m-d H:i:s', 'filter'=> Array('type'=> 'date'),'hidden'=> true);
        return $columns;

    }
}
