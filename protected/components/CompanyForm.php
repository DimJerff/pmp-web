<?php
/* 新增公司表单 */
class CompanyForm extends CFormModel
{
	public $companyName;
	public $parentCompanyId = 0;
	public $budget = 0;
	public $cost = 0;
	public $currency = 11;
	public $timezone = 0;
	public $website;
	public $telephone;
	public $postalCode = 0;
	public $country;
	public $city = 0;
	public $state = 0;
	public $address = 0;
	public $category = 0;
	public $businessLicense = 0;
	public $identityCard = 0;
	public $identityCard2 = 0;
	public $mflag = 1;
	public $creationTime = 0;
	public $status = 2;
	public $checkPoint = 0;
	public $repairPoint = 0;
	public $checkTime = 0;


	public function rules()
	{
		return array(
			array('companyName, budget, cost, currency, timezone, website, creationTime', 'required', 'on' => 'add',),
			array('parentCompanyId, budget, cost, currency, timezone, postalCode, country, city, state, category, mflag, creationTime, status, checkPoint, repairPoint, checkTime', 'numerical', 'integerOnly'=>true,  'on' => 'add',),
			array('companyName, website', 'required', 'on' => 'edit',),
			array('companyName', 'length', 'max'=>32, 'on' => 'add, edit',),
			array('website, address', 'length', 'max'=>128, 'on' => 'add',),
			array('telephone', 'length', 'min' => 11, 'max' => 16, 'on' => 'add',),
			array('businessLicense, identityCard, identityCard2', 'length', 'max'=>256, 'on' => 'add, edit',),
			array('website', 'url', 'allowEmpty' => false, 'on' => 'add, edit',),
		);
	}
}

