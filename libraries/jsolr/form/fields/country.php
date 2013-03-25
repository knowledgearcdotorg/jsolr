<?php
/**
 * Class to select lang
 *
 * @author      $LastChangedBy: bartlomiejkielbasa $
 * @package     JSolr
 *
 * @author Bartlomiej Kielbasa <bartlomiejkielbasa@wijiti.com> *
 */

jimport('jsolr.form.fields.abstract');

class JSolrFormFieldCountry extends JSolrFormFieldSelectAbstract {

    /**
     * Method to get default list of countires
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array(
            'A2' => JText::_('COM_JSOLRSEARCH_COUNTRY_A2'),
            'AF' => JText::_('COM_JSOLRSEARCH_COUNTRY_AF'),
            'AL' => JText::_('COM_JSOLRSEARCH_COUNTRY_AL'),
            'DZ' => JText::_('COM_JSOLRSEARCH_COUNTRY_DZ'),
            'AS' => JText::_('COM_JSOLRSEARCH_COUNTRY_AS'),
            'AD' => JText::_('COM_JSOLRSEARCH_COUNTRY_AD'),
            'AO' => JText::_('COM_JSOLRSEARCH_COUNTRY_AO'),
            'AI' => JText::_('COM_JSOLRSEARCH_COUNTRY_AI'),
            'AQ' => JText::_('COM_JSOLRSEARCH_COUNTRY_AQ'),
            'AG' => JText::_('COM_JSOLRSEARCH_COUNTRY_AG'),
            'AR' => JText::_('COM_JSOLRSEARCH_COUNTRY_AR'),
            'AM' => JText::_('COM_JSOLRSEARCH_COUNTRY_AM'),
            'AW' => JText::_('COM_JSOLRSEARCH_COUNTRY_AW'),
            'AU' => JText::_('COM_JSOLRSEARCH_COUNTRY_AU'),
            'AT' => JText::_('COM_JSOLRSEARCH_COUNTRY_AT'),
            'AZ' => JText::_('COM_JSOLRSEARCH_COUNTRY_AZ'),
            'BS' => JText::_('COM_JSOLRSEARCH_COUNTRY_BS'),
            'BH' => JText::_('COM_JSOLRSEARCH_COUNTRY_BH'),
            'BD' => JText::_('COM_JSOLRSEARCH_COUNTRY_BD'),
            'BB' => JText::_('COM_JSOLRSEARCH_COUNTRY_BB'),
            'BY' => JText::_('COM_JSOLRSEARCH_COUNTRY_BY'),
            'BE' => JText::_('COM_JSOLRSEARCH_COUNTRY_BE'),
            'BZ' => JText::_('COM_JSOLRSEARCH_COUNTRY_BZ'),
            'BJ' => JText::_('COM_JSOLRSEARCH_COUNTRY_BJ'),
            'BM' => JText::_('COM_JSOLRSEARCH_COUNTRY_BM'),
            'BT' => JText::_('COM_JSOLRSEARCH_COUNTRY_BT'),
            'BO' => JText::_('COM_JSOLRSEARCH_COUNTRY_BO'),
            'BA' => JText::_('COM_JSOLRSEARCH_COUNTRY_BA'),
            'BW' => JText::_('COM_JSOLRSEARCH_COUNTRY_BW'),
            'BV' => JText::_('COM_JSOLRSEARCH_COUNTRY_BV'),
            'BR' => JText::_('COM_JSOLRSEARCH_COUNTRY_BR'),
            'IO' => JText::_('COM_JSOLRSEARCH_COUNTRY_IO'),
            'BN' => JText::_('COM_JSOLRSEARCH_COUNTRY_BN'),
            'BG' => JText::_('COM_JSOLRSEARCH_COUNTRY_BG'),
            'BF' => JText::_('COM_JSOLRSEARCH_COUNTRY_BF'),
            'BI' => JText::_('COM_JSOLRSEARCH_COUNTRY_BI'),
            'KH' => JText::_('COM_JSOLRSEARCH_COUNTRY_KH'),
            'CM' => JText::_('COM_JSOLRSEARCH_COUNTRY_CM'),
            'CA' => JText::_('COM_JSOLRSEARCH_COUNTRY_CA'),
            'CV' => JText::_('COM_JSOLRSEARCH_COUNTRY_CV'),
            'KY' => JText::_('COM_JSOLRSEARCH_COUNTRY_KY'),
            'CF' => JText::_('COM_JSOLRSEARCH_COUNTRY_CF'),
            'TD' => JText::_('COM_JSOLRSEARCH_COUNTRY_TD'),
            'CL' => JText::_('COM_JSOLRSEARCH_COUNTRY_CL'),
            'CN' => JText::_('COM_JSOLRSEARCH_COUNTRY_CN'),
            'CX' => JText::_('COM_JSOLRSEARCH_COUNTRY_CX'),
            'CC' => JText::_('COM_JSOLRSEARCH_COUNTRY_CC'),
            'CO' => JText::_('COM_JSOLRSEARCH_COUNTRY_CO'),
            'KM' => JText::_('COM_JSOLRSEARCH_COUNTRY_KM'),
            'CG' => JText::_('COM_JSOLRSEARCH_COUNTRY_CG'),
            'CD' => JText::_('COM_JSOLRSEARCH_COUNTRY_CD'),
            'CK' => JText::_('COM_JSOLRSEARCH_COUNTRY_CK'),
            'CR' => JText::_('COM_JSOLRSEARCH_COUNTRY_CR'),
            'CI' => JText::_('COM_JSOLRSEARCH_COUNTRY_CI'),
            'HR' => JText::_('COM_JSOLRSEARCH_COUNTRY_HR'),
            'CU' => JText::_('COM_JSOLRSEARCH_COUNTRY_CU'),
            'CY' => JText::_('COM_JSOLRSEARCH_COUNTRY_CY'),
            'CZ' => JText::_('COM_JSOLRSEARCH_COUNTRY_CZ'),
            'DK' => JText::_('COM_JSOLRSEARCH_COUNTRY_DK'),
            'DJ' => JText::_('COM_JSOLRSEARCH_COUNTRY_DJ'),
            'DM' => JText::_('COM_JSOLRSEARCH_COUNTRY_DM'),
            'DO' => JText::_('COM_JSOLRSEARCH_COUNTRY_DO'),
            'TP' => JText::_('COM_JSOLRSEARCH_COUNTRY_TP'),
            'EC' => JText::_('COM_JSOLRSEARCH_COUNTRY_EC'),
            'EG' => JText::_('COM_JSOLRSEARCH_COUNTRY_EG'),
            'SV' => JText::_('COM_JSOLRSEARCH_COUNTRY_SV'),
            'GQ' => JText::_('COM_JSOLRSEARCH_COUNTRY_GQ'),
            'ER' => JText::_('COM_JSOLRSEARCH_COUNTRY_ER'),
            'EE' => JText::_('COM_JSOLRSEARCH_COUNTRY_EE'),
            'ET' => JText::_('COM_JSOLRSEARCH_COUNTRY_ET'),
            'FK' => JText::_('COM_JSOLRSEARCH_COUNTRY_FK'),
            'FO' => JText::_('COM_JSOLRSEARCH_COUNTRY_FO'),
            'FJ' => JText::_('COM_JSOLRSEARCH_COUNTRY_FJ'),
            'FI' => JText::_('COM_JSOLRSEARCH_COUNTRY_FI'),
            'FR' => JText::_('COM_JSOLRSEARCH_COUNTRY_FR'),
            'FX' => JText::_('COM_JSOLRSEARCH_COUNTRY_FX'),
            'GF' => JText::_('COM_JSOLRSEARCH_COUNTRY_GF'),
            'PF' => JText::_('COM_JSOLRSEARCH_COUNTRY_PF'),
            'TF' => JText::_('COM_JSOLRSEARCH_COUNTRY_TF'),
            'GA' => JText::_('COM_JSOLRSEARCH_COUNTRY_GA'),
            'GM' => JText::_('COM_JSOLRSEARCH_COUNTRY_GM'),
            'GE' => JText::_('COM_JSOLRSEARCH_COUNTRY_GE'),
            'DE' => JText::_('COM_JSOLRSEARCH_COUNTRY_DE'),
            'GH' => JText::_('COM_JSOLRSEARCH_COUNTRY_GH'),
            'GI' => JText::_('COM_JSOLRSEARCH_COUNTRY_GI'),
            'GR' => JText::_('COM_JSOLRSEARCH_COUNTRY_GR'),
            'GL' => JText::_('COM_JSOLRSEARCH_COUNTRY_GL'),
            'GD' => JText::_('COM_JSOLRSEARCH_COUNTRY_GD'),
            'GP' => JText::_('COM_JSOLRSEARCH_COUNTRY_GP'),
            'GU' => JText::_('COM_JSOLRSEARCH_COUNTRY_GU'),
            'GT' => JText::_('COM_JSOLRSEARCH_COUNTRY_GT'),
            'GN' => JText::_('COM_JSOLRSEARCH_COUNTRY_GN'),
            'GW' => JText::_('COM_JSOLRSEARCH_COUNTRY_GW'),
            'GY' => JText::_('COM_JSOLRSEARCH_COUNTRY_GY'),
            'HT' => JText::_('COM_JSOLRSEARCH_COUNTRY_HT'),
            'HM' => JText::_('COM_JSOLRSEARCH_COUNTRY_HM'),
            'VA' => JText::_('COM_JSOLRSEARCH_COUNTRY_VA'),
            'HN' => JText::_('COM_JSOLRSEARCH_COUNTRY_HN'),
            'HK' => JText::_('COM_JSOLRSEARCH_COUNTRY_HK'),
            'HU' => JText::_('COM_JSOLRSEARCH_COUNTRY_HU'),
            'IS' => JText::_('COM_JSOLRSEARCH_COUNTRY_IS'),
            'IN' => JText::_('COM_JSOLRSEARCH_COUNTRY_IN'),
            'ID' => JText::_('COM_JSOLRSEARCH_COUNTRY_ID'),
            'IR' => JText::_('COM_JSOLRSEARCH_COUNTRY_IR'),
            'IQ' => JText::_('COM_JSOLRSEARCH_COUNTRY_IQ'),
            'IE' => JText::_('COM_JSOLRSEARCH_COUNTRY_IE'),
            'IL' => JText::_('COM_JSOLRSEARCH_COUNTRY_IL'),
            'IT' => JText::_('COM_JSOLRSEARCH_COUNTRY_IT'),
            'JM' => JText::_('COM_JSOLRSEARCH_COUNTRY_JM'),
            'JP' => JText::_('COM_JSOLRSEARCH_COUNTRY_JP'),
            'JO' => JText::_('COM_JSOLRSEARCH_COUNTRY_JO'),
            'KZ' => JText::_('COM_JSOLRSEARCH_COUNTRY_KZ'),
            'KE' => JText::_('COM_JSOLRSEARCH_COUNTRY_KE'),
            'KI' => JText::_('COM_JSOLRSEARCH_COUNTRY_KI'),
            'KP' => JText::_('COM_JSOLRSEARCH_COUNTRY_KP'),
            'KR' => JText::_('COM_JSOLRSEARCH_COUNTRY_KR'),
            'KW' => JText::_('COM_JSOLRSEARCH_COUNTRY_KW'),
            'KG' => JText::_('COM_JSOLRSEARCH_COUNTRY_KG'),
            'LA' => JText::_('COM_JSOLRSEARCH_COUNTRY_LA'),
            'LV' => JText::_('COM_JSOLRSEARCH_COUNTRY_LV'),
            'LB' => JText::_('COM_JSOLRSEARCH_COUNTRY_LB'),
            'LS' => JText::_('COM_JSOLRSEARCH_COUNTRY_LS'),
            'LR' => JText::_('COM_JSOLRSEARCH_COUNTRY_LR'),
            'LY' => JText::_('COM_JSOLRSEARCH_COUNTRY_LY'),
            'LI' => JText::_('COM_JSOLRSEARCH_COUNTRY_LI'),
            'LT' => JText::_('COM_JSOLRSEARCH_COUNTRY_LT'),
            'LU' => JText::_('COM_JSOLRSEARCH_COUNTRY_LU'),
            'MO' => JText::_('COM_JSOLRSEARCH_COUNTRY_MO'),
            'MK' => JText::_('COM_JSOLRSEARCH_COUNTRY_MK'),
            'MG' => JText::_('COM_JSOLRSEARCH_COUNTRY_MG'),
            'MW' => JText::_('COM_JSOLRSEARCH_COUNTRY_MW'),
            'MY' => JText::_('COM_JSOLRSEARCH_COUNTRY_MY'),
            'MV' => JText::_('COM_JSOLRSEARCH_COUNTRY_MV'),
            'ML' => JText::_('COM_JSOLRSEARCH_COUNTRY_ML'),
            'MT' => JText::_('COM_JSOLRSEARCH_COUNTRY_MT'),
            'MH' => JText::_('COM_JSOLRSEARCH_COUNTRY_MH'),
            'MQ' => JText::_('COM_JSOLRSEARCH_COUNTRY_MQ'),
            'MR' => JText::_('COM_JSOLRSEARCH_COUNTRY_MR'),
            'MU' => JText::_('COM_JSOLRSEARCH_COUNTRY_MU'),
            'YT' => JText::_('COM_JSOLRSEARCH_COUNTRY_YT'),
            'MX' => JText::_('COM_JSOLRSEARCH_COUNTRY_MX'),
            'FM' => JText::_('COM_JSOLRSEARCH_COUNTRY_FM'),
            'MD' => JText::_('COM_JSOLRSEARCH_COUNTRY_MD'),
            'MC' => JText::_('COM_JSOLRSEARCH_COUNTRY_MC'),
            'MN' => JText::_('COM_JSOLRSEARCH_COUNTRY_MN'),
            'ME' => JText::_('COM_JSOLRSEARCH_COUNTRY_ME'),
            'MS' => JText::_('COM_JSOLRSEARCH_COUNTRY_MS'),
            'MA' => JText::_('COM_JSOLRSEARCH_COUNTRY_MA'),
            'MZ' => JText::_('COM_JSOLRSEARCH_COUNTRY_MZ'),
            'MM' => JText::_('COM_JSOLRSEARCH_COUNTRY_MM'),
            'NA' => JText::_('COM_JSOLRSEARCH_COUNTRY_NA'),
            'NR' => JText::_('COM_JSOLRSEARCH_COUNTRY_NR'),
            'NP' => JText::_('COM_JSOLRSEARCH_COUNTRY_NP'),
            'NL' => JText::_('COM_JSOLRSEARCH_COUNTRY_NL'),
            'AN' => JText::_('COM_JSOLRSEARCH_COUNTRY_AN'),
            'NC' => JText::_('COM_JSOLRSEARCH_COUNTRY_NC'),
            'NZ' => JText::_('COM_JSOLRSEARCH_COUNTRY_NZ'),
            'NI' => JText::_('COM_JSOLRSEARCH_COUNTRY_NI'),
            'NE' => JText::_('COM_JSOLRSEARCH_COUNTRY_NE'),
            'NG' => JText::_('COM_JSOLRSEARCH_COUNTRY_NG'),
            'NU' => JText::_('COM_JSOLRSEARCH_COUNTRY_NU'),
            'NF' => JText::_('COM_JSOLRSEARCH_COUNTRY_NF'),
            'MP' => JText::_('COM_JSOLRSEARCH_COUNTRY_MP'),
            'NO' => JText::_('COM_JSOLRSEARCH_COUNTRY_NO'),
            'OM' => JText::_('COM_JSOLRSEARCH_COUNTRY_OM'),
            'PK' => JText::_('COM_JSOLRSEARCH_COUNTRY_PK'),
            'PW' => JText::_('COM_JSOLRSEARCH_COUNTRY_PW'),
            'PA' => JText::_('COM_JSOLRSEARCH_COUNTRY_PA'),
            'PG' => JText::_('COM_JSOLRSEARCH_COUNTRY_PG'),
            'PY' => JText::_('COM_JSOLRSEARCH_COUNTRY_PY'),
            'PE' => JText::_('COM_JSOLRSEARCH_COUNTRY_PE'),
            'PH' => JText::_('COM_JSOLRSEARCH_COUNTRY_PH'),
            'PN' => JText::_('COM_JSOLRSEARCH_COUNTRY_PN'),
            'PL' => JText::_('COM_JSOLRSEARCH_COUNTRY_PL'),
            'PT' => JText::_('COM_JSOLRSEARCH_COUNTRY_PT'),
            'PR' => JText::_('COM_JSOLRSEARCH_COUNTRY_PR'),
            'QA' => JText::_('COM_JSOLRSEARCH_COUNTRY_QA'),
            'RE' => JText::_('COM_JSOLRSEARCH_COUNTRY_RE'),
            'RO' => JText::_('COM_JSOLRSEARCH_COUNTRY_RO'),
            'RU' => JText::_('COM_JSOLRSEARCH_COUNTRY_RU'),
            'RW' => JText::_('COM_JSOLRSEARCH_COUNTRY_RW'),
            'KN' => JText::_('COM_JSOLRSEARCH_COUNTRY_KN'),
            'LC' => JText::_('COM_JSOLRSEARCH_COUNTRY_LC'),
            'VC' => JText::_('COM_JSOLRSEARCH_COUNTRY_VC'),
            'WS' => JText::_('COM_JSOLRSEARCH_COUNTRY_WS'),
            'SM' => JText::_('COM_JSOLRSEARCH_COUNTRY_SM'),
            'ST' => JText::_('COM_JSOLRSEARCH_COUNTRY_ST'),
            'SA' => JText::_('COM_JSOLRSEARCH_COUNTRY_SA'),
            'SN' => JText::_('COM_JSOLRSEARCH_COUNTRY_SN'),
            'RS' => JText::_('COM_JSOLRSEARCH_COUNTRY_RS'),
            'SC' => JText::_('COM_JSOLRSEARCH_COUNTRY_SC'),
            'SL' => JText::_('COM_JSOLRSEARCH_COUNTRY_SL'),
            'SG' => JText::_('COM_JSOLRSEARCH_COUNTRY_SG'),
            'SK' => JText::_('COM_JSOLRSEARCH_COUNTRY_SK'),
            'SI' => JText::_('COM_JSOLRSEARCH_COUNTRY_SI'),
            'SB' => JText::_('COM_JSOLRSEARCH_COUNTRY_SB'),
            'SO' => JText::_('COM_JSOLRSEARCH_COUNTRY_SO'),
            'ZA' => JText::_('COM_JSOLRSEARCH_COUNTRY_ZA'),
            'SS' => JText::_('COM_JSOLRSEARCH_COUNTRY_SS'),
            'GS' => JText::_('COM_JSOLRSEARCH_COUNTRY_GS'),
            'ES' => JText::_('COM_JSOLRSEARCH_COUNTRY_ES'),
            'LK' => JText::_('COM_JSOLRSEARCH_COUNTRY_LK'),
            'SH' => JText::_('COM_JSOLRSEARCH_COUNTRY_SH'),
            'PM' => JText::_('COM_JSOLRSEARCH_COUNTRY_PM'),
            'SD' => JText::_('COM_JSOLRSEARCH_COUNTRY_SD'),
            'SR' => JText::_('COM_JSOLRSEARCH_COUNTRY_SR'),
            'SJ' => JText::_('COM_JSOLRSEARCH_COUNTRY_SJ'),
            'SZ' => JText::_('COM_JSOLRSEARCH_COUNTRY_SZ'),
            'SE' => JText::_('COM_JSOLRSEARCH_COUNTRY_SE'),
            'CH' => JText::_('COM_JSOLRSEARCH_COUNTRY_CH'),
            'SY' => JText::_('COM_JSOLRSEARCH_COUNTRY_SY'),
            'TW' => JText::_('COM_JSOLRSEARCH_COUNTRY_TW'),
            'TJ' => JText::_('COM_JSOLRSEARCH_COUNTRY_TJ'),
            'TZ' => JText::_('COM_JSOLRSEARCH_COUNTRY_TZ'),
            'TH' => JText::_('COM_JSOLRSEARCH_COUNTRY_TH'),
            'TG' => JText::_('COM_JSOLRSEARCH_COUNTRY_TG'),
            'TK' => JText::_('COM_JSOLRSEARCH_COUNTRY_TK'),
            'TO' => JText::_('COM_JSOLRSEARCH_COUNTRY_TO'),
            'TT' => JText::_('COM_JSOLRSEARCH_COUNTRY_TT'),
            'TN' => JText::_('COM_JSOLRSEARCH_COUNTRY_TN'),
            'TR' => JText::_('COM_JSOLRSEARCH_COUNTRY_TR'),
            'TM' => JText::_('COM_JSOLRSEARCH_COUNTRY_TM'),
            'TC' => JText::_('COM_JSOLRSEARCH_COUNTRY_TC'),
            'TV' => JText::_('COM_JSOLRSEARCH_COUNTRY_TV'),
            'UG' => JText::_('COM_JSOLRSEARCH_COUNTRY_UG'),
            'UA' => JText::_('COM_JSOLRSEARCH_COUNTRY_UA'),
            'AE' => JText::_('COM_JSOLRSEARCH_COUNTRY_AE'),
            'GB' => JText::_('COM_JSOLRSEARCH_COUNTRY_GB'),
            'US' => JText::_('COM_JSOLRSEARCH_COUNTRY_US'),
            'UM' => JText::_('COM_JSOLRSEARCH_COUNTRY_UM'),
            'UY' => JText::_('COM_JSOLRSEARCH_COUNTRY_UY'),
            'UZ' => JText::_('COM_JSOLRSEARCH_COUNTRY_UZ'),
            'VU' => JText::_('COM_JSOLRSEARCH_COUNTRY_VU'),
            'VE' => JText::_('COM_JSOLRSEARCH_COUNTRY_VE'),
            'VN' => JText::_('COM_JSOLRSEARCH_COUNTRY_VN'),
            'VG' => JText::_('COM_JSOLRSEARCH_COUNTRY_VG'),
            'VI' => JText::_('COM_JSOLRSEARCH_COUNTRY_VI'),
            'WF' => JText::_('COM_JSOLRSEARCH_COUNTRY_WF'),
            'EH' => JText::_('COM_JSOLRSEARCH_COUNTRY_EH'),
            'YE' => JText::_('COM_JSOLRSEARCH_COUNTRY_YE'),
            'ZM' => JText::_('COM_JSOLRSEARCH_COUNTRY_ZM'),
            'ZW' => JText::_('COM_JSOLRSEARCH_COUNTRY_ZW')
        );
    }

    /**
     * @inheritdoc
     */
    public function getFilter()
    {
        $facet = (string)$this->element['facet'];

        $filter = '';

        if (!empty($this->value)) {
            if (is_string($this->value)) {
                $filter = $facet . ':' . $this->value;
            } elseif (is_array($this->value)) {
                $filter = $facet . ':' . implode(' OR ', $this->value);
            }
        }        

        return $filter;
    }

    function getValueText()
    {
        if (!is_array($this->value) || count($this->value) == 0) {
            return JText::_("COM_JSOLRSEARCH_LANGUAGE_ALL");
        }

        $result = array();
        $options = $this->getFinalOptions();

        foreach ($this->value as $v) {
            $result[] = $options[$v];
        }

        return implode(', ', $result);
    }
}