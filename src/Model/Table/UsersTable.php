<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @property \Cake\ORM\Association\HasMany $SocialProfiles
 *
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class UsersTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('users');
        $this->displayField('id');
        $this->primaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('ADmad/HybridAuth.SocialProfiles');
        \Cake\Event\EventManager::instance()->on('HybridAuth.newUser', [$this, 'createUser']);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->isUnique(['email']));
        $rules->add($rules->isUnique(['username']));

        return $rules;
    }

    public function createUser(\Cake\Event\Event $event){
        $profile = $event->data()['profile'];
        $user = $this->newEntity(['provider'=>$profile['provider'],'identifier'=>$profile['identifier'],'email'=>$profile['email']]);
        try{
            $users = $this->save($user);
        }catch(\Exception $ex){
            throw $ex; 
        }
        
        if(!$user){
            throw new \RuntimeException("Unable to save user");
        }

        return $user;

    }

    public function setLastLogin($userId = 0)
    {
       if (!$userId) {
            return false;
       }
       $user =  $this->get($userId);
       $user->last_login = (new \DateTime())->format('Y-m-d H:i:s');
       return $this->save($user);
    }

    public function setLastLogout($userId = 0)
    {
       if (!$userId) {
            return false;
       }
       $user =  $this->get($userId);
       $user->last_logout = (new \DateTime())->format('Y-m-d H:i:s');
       return $this->save($user);
    }

    public function setDeleted($userId = 0)
    {
        if (!$userId) {
            return false;
        }
        $user = $this->get($userId);
        $user->deleted = true;
        return $this->save($user);
    }

    public function updateLoginCount($userId = 0)
    {
        if (!$userId) {
            return false;
        }
        $user = $this->get($userId);
        $user->login_count = $user->login_count + 1;
        return $this->save($user);
    }

    public function setFirstName($userId = 0, $name)
    {
        if (!$userId) {
            return false;
        }
        $user = $this->get($userId);
        $user->first_name = $name;
        return $this->save($user);
    }

    public function setLastName($userId = 0, $name)
    {
        if (!$userId) {
            return false;
        }
        $user = $this->get($userId);
        $user->last_name = $name;
        return $this->save($user);
    }
    
    public function getUserFullName($userId = 0)
    {
        if (!$userId) {
            return false;
        }
        $user = $this->get($userId);
        return $user->first_name . ' ' . $user->last_name;
    }
}
