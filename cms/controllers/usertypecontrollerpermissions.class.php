<?php

namespace WebFW\CMS\Controllers;

use WebFW\CMS\Classes\ListHelper;
use WebFW\CMS\Controller;
use WebFW\CMS\DBLayer\ListFetchers\UserTypeControllerPermissions as LFUTCP;
use WebFW\CMS\DBLayer\UserTypeControllerPermissions as TGUTCP;
use WebFW\Core\Classes\HTML\Input;
use WebFW\CMS\Classes\EditTab;
use WebFW\Core\Classes\HTML\Select;
use WebFW\CMS\DBLayer\ListFetchers\UserType as LFUserType;

class UserTypeControllerPermissions extends Controller
{
    protected function init()
    {
        parent::init();

        $this->listFetcher = new LFUTCP();
        $this->tableGateway = new TGUTCP();
    }

    public function initList()
    {
        parent::initList();

        $this->sort = array(
            'user_type_id' => 'ASC',
        );

        $this->addListColumn('strUserType', 'User Type');
        $this->addListColumn('strController', 'Controller');
        $this->addListColumn('strSelect', 'S', true);
        $this->addListColumn('strInsert', 'I', true);
        $this->addListColumn('strUpdate', 'U', true);
        $this->addListColumn('strDelete', 'D', true);
        $this->addListColumn('strCustom', 'C', true);
    }

    public function initEdit()
    {
        parent::initEdit();

        $tab = new EditTab('default');

        $userTypeLf = new LFUserType();
        $userTypes = ListHelper::GetKeyValueList($userTypeLf->getList(null, array('user_type_id' => 'ASC')), 'user_type_id', 'caption');

        $tab->addField(new Select('user_type_id', null, $userTypes, null, 'user_type_id'), 'User Type', null, true);
        $tab->addField(new Input('select', null, 'checkbox', null, 'select'), 'Select', null, false);
        $tab->addField(new Input('controller', null, 'text', null, 'controller'), 'Controller', null, true);
        $tab->addField(new Input('insert', null, 'checkbox', null, 'insert'), 'Insert', null, false);
        $tab->addField(new Input('namespace', null, 'text', null, 'namespace'), 'Namespace', null, true);
        $tab->addField(new Input('update', null, 'checkbox', null, 'update'), 'Update', null, false);
        $tab->addField(new Input('custom', null, 'checkbox', null, 'custom'), 'Custom', null, true);
        $tab->addField(new Input('delete', null, 'checkbox', null, 'delete'), 'Delete', null, false);

        $this->editTabs[] = $tab;
    }

    public function processList(&$list)
    {
        foreach ($list as &$item) {
            $item['strUserType'] = $item->getUserTypeCaption();
            $item['strController'] = $item->getControllerCaption();
            $item['strSelect'] = $item->checkTypePermissions(TGUTCP::TYPE_SELECT) ? 'S' : '';
            $item['strInsert'] = $item->checkTypePermissions(TGUTCP::TYPE_INSERT) ? 'I' : '';
            $item['strUpdate'] = $item->checkTypePermissions(TGUTCP::TYPE_UPDATE) ? 'U' : '';
            $item['strDelete'] = $item->checkTypePermissions(TGUTCP::TYPE_DELETE) ? 'D' : '';
            $item['strCustom'] = $item->checkTypePermissions(TGUTCP::TYPE_CUSTOM) ? 'C' : '';
        }
    }

    protected function beforeSave()
    {
        $this->tableGateway->permissions = 0;

        if ($this->tableGateway->select === '1') {
            $this->tableGateway->permissions = $this->tableGateway->permissions | TGUTCP::TYPE_SELECT;
        }

        if ($this->tableGateway->insert === '1') {
            $this->tableGateway->permissions = $this->tableGateway->permissions | TGUTCP::TYPE_INSERT;
        }

        if ($this->tableGateway->update === '1') {
            $this->tableGateway->permissions = $this->tableGateway->permissions | TGUTCP::TYPE_UPDATE;
        }

        if ($this->tableGateway->delete === '1') {
            $this->tableGateway->permissions = $this->tableGateway->permissions | TGUTCP::TYPE_DELETE;
        }

        if ($this->tableGateway->custom === '1') {
            $this->tableGateway->permissions = $this->tableGateway->permissions | TGUTCP::TYPE_CUSTOM;
        }
    }

    protected function afterLoad()
    {
        $this->tableGateway->select = $this->tableGateway->checkTypePermissions(TGUTCP::TYPE_SELECT);
        $this->tableGateway->insert = $this->tableGateway->checkTypePermissions(TGUTCP::TYPE_INSERT);
        $this->tableGateway->update = $this->tableGateway->checkTypePermissions(TGUTCP::TYPE_UPDATE);
        $this->tableGateway->delete = $this->tableGateway->checkTypePermissions(TGUTCP::TYPE_DELETE);
        $this->tableGateway->custom = $this->tableGateway->checkTypePermissions(TGUTCP::TYPE_CUSTOM);
    }
}
