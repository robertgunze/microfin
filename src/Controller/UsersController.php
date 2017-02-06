<?php
namespace App\Controller;

use App\Controller\AppController;
use App\Model\Serengeti;
/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    public function initialize () {
        parent::initialize();
        $this->Auth->allow(['logout']);
    }

    public function login()
    {
        $this->viewBuilder()->layout('login');
        if($this->request->is('post') || $this->request->query('provider')){
            $user = $this->Auth->identify();
            if ($user) {
                //debug($user);exit;
                $provider = $this->request->query('provider');
                if($provider){
                    //if provider is set: not email login
                    if (!isset($user['first_name'])) {
                        $firstName = $user['social_profile']['first_name'];
                        $this->Users->setFirstName($user['id'],$firstName);
                    }
                    if (!isset($user['last_name'])) {
                        $lastName = $user['social_profile']['last_name'];
                        $this->Users->setLastName($user['id'],$lastName);
                    }
                }

                if (!$user['active']) {
                    $this->Flash->error(__('Your account is pending activation.'));
                } else {
                    $this->Auth->setUser($user);
                    $this->Users->setLastLogin($user['id']);
                    $this->Users->updateLoginCount($user['id']);
                    //api token getting
                    $apiCredentials = (new Serengeti())->getApiAccessToken();
                    $apiCredentialsObj = json_decode($apiCredentials);
                    $token = $apiCredentialsObj->token;
                    $session = $this->request->session();
                    $session->write('Auth.token',$token);
                    
                    return $this->redirect($this->Auth->redirectUrl());
                }
               
            }else {
                $this->Flash->error(__('Invalid username or password, try again'));
            }
        }
    }

    public function logout()
    {
        $this->autoRender = false;
        $userId = $this->Auth->user('id');
        $this->Auth->logout();
        //$this->Users->setLastLogout($userId);
        return $this->redirect('users/login');
    }

    /**
     * Index method
     *
     * @return \Cake\Network\Response|null
     */
    public function index()
    {
        $users = $this->paginate($this->Users->find()->where(['deleted'=>false]));

        $this->set(compact('users'));
        $this->set('_serialize', ['users']);
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['SocialProfiles']
        ]);

        $activatedBy = $this->Users->getUserFullName($user['activated_by']);
        $this->set('activatedBy',$activatedBy);
        $this->set('user', $user);
        $this->set('_serialize', ['user']);
    }

    /**
     * Add method
     *
     * @return \Cake\Network\Response|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be saved. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Network\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        //$user = $this->Users->get($id);
        if ($this->Users->setDeleted($id)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function activate($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $loggedInUserId = $this->Auth->user('id');
            if(!$loggedInUserId){
                $loggedInUserId = 0;
            }
            $epoch = (new \DateTime())->format('Y-m-d H:i:s');
            $user = $this->Users->patchEntity($user, ['active'=>true,'activated_at'=>$epoch,'activated_by'=>$loggedInUserId]);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been activated.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be activated. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }

    public function deactivate($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, ['active'=>false]);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been deactivated.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The user could not be deactivated. Please, try again.'));
            }
        }
        $this->set(compact('user'));
        $this->set('_serialize', ['user']);
    }
}
