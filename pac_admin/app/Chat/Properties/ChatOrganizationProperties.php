<?php
namespace App\Chat\Properties;

class ChatOrganizationProperties extends AbstractProperties {

    const API_ID_TYPE = "Organization_Type";

    /**
     * 組織の種類
     *
     * 'community' : 'Community',
     * 'enterprise' : 'Enterprise',
     * 'government' : 'Government',
     * 'nonprofit' : 'Nonprofit',
     */
    public function type_code(string $value = null) {
        return $this->_getset(self::API_ID_TYPE, func_get_args(), "enterprise");
    }

    const API_ID_NAME = "Organization_Name";

    /**
     * 組織名
     *
     * @return string
     */
    public function name(string $value = null) {
        return $this->_getset(self::API_ID_NAME, func_get_args());
    }

    const API_ID_INDUSTRY = "Industry";

    /**
     * 事業内容
     *
     * 'aerospaceDefense' : 'Aerospace_and_Defense',
     * 'blockchain' : 'Blockchain',
     * 'consulting' : 'Consulting',
     * 'consumerGoods' : 'Consumer_Packaged_Goods',
     * 'contactCenter' : 'Contact_Center',
     * 'education' : 'Education',
     * 'entertainment' : 'Entertainment',
     * 'financialServices' : 'Financial_Services',
     * 'gaming' : 'Gaming',
     * 'healthcare' : 'Healthcare',
     * 'hospitalityBusinness' : 'Hospitality_Businness',
     * 'insurance' : 'Insurance',
     * 'itSecurity' : 'It_Security',
     * 'logistics' : 'Logistics',
     * 'manufacturing' : 'Manufacturing',
     * 'media' : 'Media',
     * 'pharmaceutical' : 'Pharmaceutical',
     * 'realEstate' : 'Real_Estate',
     * 'religious' : 'Religious',
     * 'retail' : 'Retail',
     * 'socialNetwork' : 'Social_Network',
     * 'technologyProvider' : 'Technology_Provider',
     * 'technologyServices' : 'Technology_Services',
     * 'telecom' : 'Telecom',
     * 'utilities' : 'Utilities',
     * 'other' : 'Other'
     *
     * @param string $value
     *
     */
    public function industry(string $value = null) {
        return $this->_getset(self::API_ID_INDUSTRY, func_get_args(), "other");
    }

    const API_ID_SIZE = "Size";

    /**
     * 事業規模
     *
     * '0' : '1-10 people',
	 * '1' : '11-50 people',
     * '2' : '51-100 people',
	 * '3' : '101-250 people',
	 * '4' : '251-500 people',
	 * '5' : '501-1000 people',
	 * '6' : '1001-4000 people',
	 * '7' : '4000 or more people',
     *
     * @param string $value
     */
    public function size(string $value = null) {
        return $this->_getset(self::API_ID_SIZE, func_get_args(), 0);
    }


    const API_ID_COUNTRY = "Country";

    /**
     * 国
     *
     * "japan"
     *
     * @param string $value
     */
    public function country(string $value = null) {
        return $this->_getset(self::API_ID_COUNTRY, func_get_args(), "japan");
    }


    const API_ID_WEBSITE = "Website";

    /**
     * 自社サイトURL
     *
     * @param string $value
     */
    public function website(string $value = null) {
        return $this->_getset(self::API_ID_WEBSITE, func_get_args());
    }
}
