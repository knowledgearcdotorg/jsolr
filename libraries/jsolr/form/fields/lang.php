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

class JSolrFormFieldLang extends JSolrFormFieldSelectAbstract {

    /**
     * Method to get default list of languages
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array(
            'ar' => JText::_('COM_JSOLR_LANG_AR'),
            'ar-AE' => JText::_('COM_JSOLR_LANG_AR_AE'),
            'ar-BH' => JText::_('COM_JSOLR_LANG_AR_BH'),
            'ar-DZ' => JText::_('COM_JSOLR_LANG_AR_DZ'),
            'ar-EG' => JText::_('COM_JSOLR_LANG_AR_EG'),
            'ar-IQ' => JText::_('COM_JSOLR_LANG_AR_IQ'),
            'ar-JO' => JText::_('COM_JSOLR_LANG_AR_JO'),
            'ar-KW' => JText::_('COM_JSOLR_LANG_AR_KW'),
            'ar-LB' => JText::_('COM_JSOLR_LANG_AR_LB'),
            'ar-LY' => JText::_('COM_JSOLR_LANG_AR_LY'),
            'ar-MA' => JText::_('COM_JSOLR_LANG_AR_MA'),
            'ar-OM' => JText::_('COM_JSOLR_LANG_AR_OM'),
            'ar-QA' => JText::_('COM_JSOLR_LANG_AR_QA'),
            'ar-SA' => JText::_('COM_JSOLR_LANG_AR_SA'),
            'ar-SD' => JText::_('COM_JSOLR_LANG_AR_SD'),
            'ar-SY' => JText::_('COM_JSOLR_LANG_AR_SY'),
            'ar-TN' => JText::_('COM_JSOLR_LANG_AR_TN'),
            'ar-YE' => JText::_('COM_JSOLR_LANG_AR_YE'),
            'be' => JText::_('COM_JSOLR_LANG_BE'),
            'be-BY' => JText::_('COM_JSOLR_LANG_BE_BY'),
            'bg' => JText::_('COM_JSOLR_LANG_BG'),
            'bg-BG' => JText::_('COM_JSOLR_LANG_BG_BG'),
            'ca' => JText::_('COM_JSOLR_LANG_CA'),
            'ca-ES' => JText::_('COM_JSOLR_LANG_CA_ES'),
            'cs' => JText::_('COM_JSOLR_LANG_CS'),
            'cs-CZ' => JText::_('COM_JSOLR_LANG_CS_CZ'),
            'da' => JText::_('COM_JSOLR_LANG_DA'),
            'da-DK' => JText::_('COM_JSOLR_LANG_DA_DK'),
            'de' => JText::_('COM_JSOLR_LANG_DE'),
            'de-AT' => JText::_('COM_JSOLR_LANG_DE_AT'),
            'de-CH' => JText::_('COM_JSOLR_LANG_DE_CH'),
            'de-DE' => JText::_('COM_JSOLR_LANG_DE_DE'),
            'de-LU' => JText::_('COM_JSOLR_LANG_DE_LU'),
            'el' => JText::_('COM_JSOLR_LANG_EL'),
            'el-CY' => JText::_('COM_JSOLR_LANG_EL_CY'),
            'el-GR' => JText::_('COM_JSOLR_LANG_EL_GR'),
            'en' => JText::_('COM_JSOLR_LANG_EN'),
            'en-AU' => JText::_('COM_JSOLR_LANG_EN_AU'),
            'en-CA' => JText::_('COM_JSOLR_LANG_EN_CA'),
            'en-GB' => JText::_('COM_JSOLR_LANG_EN_GB'),
            'en-IE' => JText::_('COM_JSOLR_LANG_EN_IE'),
            'en-IN' => JText::_('COM_JSOLR_LANG_EN_IN'),
            'en-MT' => JText::_('COM_JSOLR_LANG_EN_MT'),
            'en-NZ' => JText::_('COM_JSOLR_LANG_EN_NZ'),
            'en-PH' => JText::_('COM_JSOLR_LANG_EN_PH'),
            'en-SG' => JText::_('COM_JSOLR_LANG_EN_SG'),
            'en-US' => JText::_('COM_JSOLR_LANG_EN_US'),
            'en-ZA' => JText::_('COM_JSOLR_LANG_EN_ZA'),
            'es' => JText::_('COM_JSOLR_LANG_ES'),
            'es-AR' => JText::_('COM_JSOLR_LANG_ES_AR'),
            'es-BO' => JText::_('COM_JSOLR_LANG_ES_BO'),
            'es-CL' => JText::_('COM_JSOLR_LANG_ES_CL'),
            'es-CO' => JText::_('COM_JSOLR_LANG_ES_CO'),
            'es-CR' => JText::_('COM_JSOLR_LANG_ES_CR'),
            'es-DO' => JText::_('COM_JSOLR_LANG_ES_DO'),
            'es-EC' => JText::_('COM_JSOLR_LANG_ES_EC'),
            'es-ES' => JText::_('COM_JSOLR_LANG_ES_ES'),
            'es-GT' => JText::_('COM_JSOLR_LANG_ES_GT'),
            'es-HN' => JText::_('COM_JSOLR_LANG_ES_HN'),
            'es-MX' => JText::_('COM_JSOLR_LANG_ES_MX'),
            'es-NI' => JText::_('COM_JSOLR_LANG_ES_NI'),
            'es-PA' => JText::_('COM_JSOLR_LANG_ES_PA'),
            'es-PE' => JText::_('COM_JSOLR_LANG_ES_PE'),
            'es-PR' => JText::_('COM_JSOLR_LANG_ES_PR'),
            'es-PY' => JText::_('COM_JSOLR_LANG_ES_PY'),
            'es-SV' => JText::_('COM_JSOLR_LANG_ES_SV'),
            'es-US' => JText::_('COM_JSOLR_LANG_ES_US'),
            'es-UY' => JText::_('COM_JSOLR_LANG_ES_UY'),
            'es-VE' => JText::_('COM_JSOLR_LANG_ES_VE'),
            'et' => JText::_('COM_JSOLR_LANG_ET'),
            'et-EE' => JText::_('COM_JSOLR_LANG_ET_EE'),
            'fi' => JText::_('COM_JSOLR_LANG_FI'),
            'fi-FI' => JText::_('COM_JSOLR_LANG_FI_FI'),
            'fr' => JText::_('COM_JSOLR_LANG_FR'),
            'fr-BE' => JText::_('COM_JSOLR_LANG_FR_BE'),
            'fr-CA' => JText::_('COM_JSOLR_LANG_FR_CA'),
            'fr-CH' => JText::_('COM_JSOLR_LANG_FR_CH'),
            'fr-FR' => JText::_('COM_JSOLR_LANG_FR_FR'),
            'fr-LU' => JText::_('COM_JSOLR_LANG_FR_LU'),
            'ga' => JText::_('COM_JSOLR_LANG_GA'),
            'ga-IE' => JText::_('COM_JSOLR_LANG_GA_IE'),
            'hi-IN' => JText::_('COM_JSOLR_LANG_HI_IN'),
            'hr' => JText::_('COM_JSOLR_LANG_HR'),
            'hr-HR' => JText::_('COM_JSOLR_LANG_HR_HR'),
            'hu' => JText::_('COM_JSOLR_LANG_HU'),
            'hu-HU' => JText::_('COM_JSOLR_LANG_HU_HU'),
            'in' => JText::_('COM_JSOLR_LANG_IN'),
            'in-ID' => JText::_('COM_JSOLR_LANG_IN_ID'),
            'is' => JText::_('COM_JSOLR_LANG_IS'),
            'is-IS' => JText::_('COM_JSOLR_LANG_IS_IS'),
            'it' => JText::_('COM_JSOLR_LANG_IT'),
            'it-CH' => JText::_('COM_JSOLR_LANG_IT_CH'),
            'it-IT' => JText::_('COM_JSOLR_LANG_IT_IT'),
            'iw' => JText::_('COM_JSOLR_LANG_IW'),
            'iw-IL' => JText::_('COM_JSOLR_LANG_IW_IL'),
            'ja' => JText::_('COM_JSOLR_LANG_JA'),
            'ja-JP' => JText::_('COM_JSOLR_LANG_JA_JP'),
            'ja-JP-JP-u-ca-japanese' => JText::_('COM_JSOLR_LANG_JA_JP_JP_U_CA_JAPANESE'),
            'ko' => JText::_('COM_JSOLR_LANG_KO'),
            'ko-KR' => JText::_('COM_JSOLR_LANG_KO_KR'),
            'lt' => JText::_('COM_JSOLR_LANG_LT'),
            'lt-LT' => JText::_('COM_JSOLR_LANG_LT_LT'),
            'lv' => JText::_('COM_JSOLR_LANG_LV'),
            'lv-LV' => JText::_('COM_JSOLR_LANG_LV_LV'),
            'mk' => JText::_('COM_JSOLR_LANG_MK'),
            'mk-MK' => JText::_('COM_JSOLR_LANG_MK_MK'),
            'ms' => JText::_('COM_JSOLR_LANG_MS'),
            'ms-MY' => JText::_('COM_JSOLR_LANG_MS_MY'),
            'mt' => JText::_('COM_JSOLR_LANG_MT'),
            'mt-MT' => JText::_('COM_JSOLR_LANG_MT_MT'),
            'nl' => JText::_('COM_JSOLR_LANG_NL'),
            'nl-BE' => JText::_('COM_JSOLR_LANG_NL_BE'),
            'nl-NL' => JText::_('COM_JSOLR_LANG_NL_NL'),
            'no' => JText::_('COM_JSOLR_LANG_NO'),
            'no-NO' => JText::_('COM_JSOLR_LANG_NO_NO'),
            'no-NO-NY' => JText::_('COM_JSOLR_LANG_NO_NO_NY'),
            'pl' => JText::_('COM_JSOLR_LANG_PL'),
            'pl-PL' => JText::_('COM_JSOLR_LANG_PL_PL'),
            'pt' => JText::_('COM_JSOLR_LANG_PT'),
            'pt-BR' => JText::_('COM_JSOLR_LANG_PT_BR'),
            'pt-PT' => JText::_('COM_JSOLR_LANG_PT_PT'),
            'ro' => JText::_('COM_JSOLR_LANG_RO'),
            'ro-RO' => JText::_('COM_JSOLR_LANG_RO_RO'),
            'ru' => JText::_('COM_JSOLR_LANG_RU'),
            'ru-RU' => JText::_('COM_JSOLR_LANG_RU_RU'),
            'sk' => JText::_('COM_JSOLR_LANG_SK'),
            'sk-SK' => JText::_('COM_JSOLR_LANG_SK_SK'),
            'sl' => JText::_('COM_JSOLR_LANG_SL'),
            'sl-SI' => JText::_('COM_JSOLR_LANG_SL_SI'),
            'sq' => JText::_('COM_JSOLR_LANG_SQ'),
            'sq-AL' => JText::_('COM_JSOLR_LANG_SQ_AL'),
            'sr' => JText::_('COM_JSOLR_LANG_SR'),
            'sr_Latn' => JText::_('COM_JSOLR_LANG_SR_LATN'),
            'sr-BA' => JText::_('COM_JSOLR_LANG_SR_BA'),
            'sr-BA-Latn' => JText::_('COM_JSOLR_LANG_SR_BA_LATN'),
            'sr-CS' => JText::_('COM_JSOLR_LANG_SR_CS'),
            'sr-ME' => JText::_('COM_JSOLR_LANG_SR_ME'),
            'sr-ME-Latn' => JText::_('COM_JSOLR_LANG_SR_ME_LATN'),
            'sr-RS' => JText::_('COM_JSOLR_LANG_SR_RS'),
            'sr-RS-Latn' => JText::_('COM_JSOLR_LANG_SR_RS_LATN'),
            'sv' => JText::_('COM_JSOLR_LANG_SV'),
            'sv-SE' => JText::_('COM_JSOLR_LANG_SV_SE'),
            'th' => JText::_('COM_JSOLR_LANG_TH'),
            'th-TH' => JText::_('COM_JSOLR_LANG_TH_TH'),
            'th-TH-TH-u-nu-thai' => JText::_('COM_JSOLR_LANG_TH_TH_TH_U_NU_THAI'),
            'tr' => JText::_('COM_JSOLR_LANG_TR'),
            'tr-TR' => JText::_('COM_JSOLR_LANG_TR_TR'),
            'uk' => JText::_('COM_JSOLR_LANG_UK'),
            'uk-UA' => JText::_('COM_JSOLR_LANG_UK_UA'),
            'vi' => JText::_('COM_JSOLR_LANG_VI'),
            'vi-VN' => JText::_('COM_JSOLR_LANG_VI_VN'),
            'zh' => JText::_('COM_JSOLR_LANG_ZH'),
            'zh-CN' => JText::_('COM_JSOLR_LANG_ZH_CN'),
            'zh-HK' => JText::_('COM_JSOLR_LANG_ZH_HK'),
            'zh-SG' => JText::_('COM_JSOLR_LANG_ZH_SG'),
            'zh-TW' => JText::_('COM_JSOLR_LANG_ZH_TW'),
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
            $result[] = JArrayHelper::getValue($options,$v);
        }

        return implode(', ', $result);
    }
}