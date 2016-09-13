<?php

namespace Xxam\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository
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
        $fields[]=Array('name'=> 'username','type'=>'string');
        $fields[]=Array('name'=> 'email','type'=>'string');
        $fields[]=Array('name'=> 'last_login','type'=>'date', 'dateFormat'=>'Y-m-d H:i:s');
        $fields[]=Array('name'=> 'locked','type'=>'boolean');
        $fields[]=Array('name'=> 'expired','type'=>'boolean');
        $fields[]=Array('name'=> 'expires_at','type'=>'date', 'dateFormat'=>'Y-m-d H:i:s');
        $fields[]=Array('name'=> 'credentials_expired','type'=>'boolean');
        //$fields[]=Array('name'=> 'password','type'=>'auto');
        $fields[]=Array('name'=> 'credentials_expire_at','type'=>'date', 'dateFormat'=>'Y-m-d H:i:s');
        $fields[]=Array('name'=> 'created','type'=>'date', 'dateFormat'=>'Y-m-d H:i:s');
        $fields[]=Array('name'=> 'updated','type'=>'date', 'dateFormat'=>'Y-m-d H:i:s');
        $fields[]=Array('name'=> 'groups','type'=>'auto', 'convert'=>'function(value){ if (value && Ext.isArray(value)){ return value; }else if( value && !Ext.isArray(value) ){ return [value]; }else{ return []; }}');
        $fields[]=Array('name'=> 'roles','type'=>'auto', 'convert'=>'function(value){ if (value && Ext.isArray(value)){ return value; }else if( value && !Ext.isArray(value) ){ return [value]; }else{ return []; }}');
        $fields[]=Array('name'=> 'timezone','type'=>'string');
        return $fields;
        
    }
    public function getGridColumns(){
        $columns=Array();
        $columns[]=Array('text'=> 'Id','dataIndex'=> 'id', 'filter'=> Array('type'=> 'number'),'hidden'=> true);
        $columns[]=Array('text'=> 'Username','flex'=> 1,'dataIndex'=> 'username', 'filter'=> Array('type'=> 'string'));
        $columns[]=Array('text'=> 'Email','flex'=> 1,'dataIndex'=> 'email', 'filter'=> Array('type'=> 'string'));
        $columns[]=Array('text'=> 'Last login','flex'=> 1,'dataIndex'=> 'last_login', 'xtype'=> 'datecolumn', 'format'=>'Y-m-d H:i:s', 'filter'=> Array('type'=> 'date'),'hidden'=> true);
        $columns[]=Array('text'=> 'Locked','dataIndex'=> 'locked', 'filter'=> Array('type'=> 'boolean'),'hidden'=> false);
        $columns[]=Array('text'=> 'Expired','dataIndex'=> 'expired', 'filter'=> Array('type'=> 'boolean'),'hidden'=> false);
        $columns[]=Array('text'=> 'Expires','dataIndex'=> 'expires_at', 'xtype'=> 'datecolumn', 'format'=>'Y-m-d H:i:s', 'filter'=> Array('type'=> 'date'),'hidden'=> true);
        $columns[]=Array('text'=> 'Credentials expired','dataIndex'=> 'credential_expired', 'filter'=> Array('type'=> 'boolean'),'hidden'=> true);
        $columns[]=Array('text'=> 'Credentials expire','dataIndex'=> 'credential_expires_at', 'xtype'=> 'datecolumn', 'format'=>'Y-m-d H:i:s', 'filter'=> Array('type'=> 'date'),'hidden'=> true);
        $columns[]=Array('text'=> 'Groups','dataIndex'=> 'groups', 'filter'=> Array('type'=> 'string'),'hidden'=> false);
        $columns[]=Array('text'=> 'Timezone','dataIndex'=> 'timezone', 'filter'=> Array('type'=> 'string'));
        $columns[]=Array('text'=> 'Created','dataIndex'=> 'created', 'xtype'=> 'datecolumn', 'format'=>'Y-m-d H:i:s', 'filter'=> Array('type'=> 'date'),'hidden'=> true);
        $columns[]=Array('text'=> 'Updated','dataIndex'=> 'updated', 'xtype'=> 'datecolumn', 'format'=>'Y-m-d H:i:s', 'filter'=> Array('type'=> 'date'),'hidden'=> true);
        return $columns;
        
    }
}
