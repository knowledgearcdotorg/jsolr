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
     * Method to get default list of countires
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array(
            'af-ZA' => JText::_('COM_JSOLR_LANG_AF_ZA'),
            'am-ET' => JText::_('COM_JSOLR_LANG_AM_ET'),
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
            'arn-CL' => JText::_('COM_JSOLR_LANG_ARN_CL'),
            'ar-OM' => JText::_('COM_JSOLR_LANG_AR_OM'),
            'ar-QA' => JText::_('COM_JSOLR_LANG_AR_QA'),
            'ar-SA' => JText::_('COM_JSOLR_LANG_AR_SA'),
            'ar-SY' => JText::_('COM_JSOLR_LANG_AR_SY'),
            'ar-TN' => JText::_('COM_JSOLR_LANG_AR_TN'),
            'ar-YE' => JText::_('COM_JSOLR_LANG_AR_YE'),
            'as-IN' => JText::_('COM_JSOLR_LANG_AS_IN'),
            'az-Cyrl-AZ' => JText::_('COM_JSOLR_LANG_AZ_CYRL_AZ'),
            'az-Latn-AZ' => JText::_('COM_JSOLR_LANG_AZ_LATN_AZ'),
            'ba-RU' => JText::_('COM_JSOLR_LANG_BA_RU'),
            'be-BY' => JText::_('COM_JSOLR_LANG_BE_BY'),
            'bg-BG' => JText::_('COM_JSOLR_LANG_BG_BG'),
            'bn-BD' => JText::_('COM_JSOLR_LANG_BN_BD'),
            'bn-IN' => JText::_('COM_JSOLR_LANG_BN_IN'),
            'bo-CN' => JText::_('COM_JSOLR_LANG_BO_CN'),
            'br-FR' => JText::_('COM_JSOLR_LANG_BR_FR'),
            'bs-Cyrl-BA' => JText::_('COM_JSOLR_LANG_BS_CYRL_BA'),
            'bs-Latn-BA' => JText::_('COM_JSOLR_LANG_BS_LATN_BA'),
            'ca-ES' => JText::_('COM_JSOLR_LANG_CA_ES'),
            'co-FR' => JText::_('COM_JSOLR_LANG_CO_FR'),
            'cs-CZ' => JText::_('COM_JSOLR_LANG_CS_CZ'),
            'cy-GB' => JText::_('COM_JSOLR_LANG_CY_GB'),
            'da-DK' => JText::_('COM_JSOLR_LANG_DA_DK'),
            'de-AT' => JText::_('COM_JSOLR_LANG_DE_AT'),
            'de-CH' => JText::_('COM_JSOLR_LANG_DE_CH'),
            'de-DE' => JText::_('COM_JSOLR_LANG_DE_DE'),
            'de-LI' => JText::_('COM_JSOLR_LANG_DE_LI'),
            'de-LU' => JText::_('COM_JSOLR_LANG_DE_LU'),
            'dsb-DE' => JText::_('COM_JSOLR_LANG_DSB_DE'),
            'dv-MV' => JText::_('COM_JSOLR_LANG_DV_MV'),
            'el-GR' => JText::_('COM_JSOLR_LANG_EL_GR'),
            'en-029' => JText::_('COM_JSOLR_LANG_EN_029'),
            'en-AU' => JText::_('COM_JSOLR_LANG_EN_AU'),
            'en-BZ' => JText::_('COM_JSOLR_LANG_EN_BZ'),
            'en-CA' => JText::_('COM_JSOLR_LANG_EN_CA'),
            'en-GB' => JText::_('COM_JSOLR_LANG_EN_GB'),
            'en-IE' => JText::_('COM_JSOLR_LANG_EN_IE'),
            'en-IN' => JText::_('COM_JSOLR_LANG_EN_IN'),
            'en-JM' => JText::_('COM_JSOLR_LANG_EN_JM'),
            'en-MY' => JText::_('COM_JSOLR_LANG_EN_MY'),
            'en-NZ' => JText::_('COM_JSOLR_LANG_EN_NZ'),
            'en-PH' => JText::_('COM_JSOLR_LANG_EN_PH'),
            'en-SG' => JText::_('COM_JSOLR_LANG_EN_SG'),
            'en-TT' => JText::_('COM_JSOLR_LANG_EN_TT'),
            'en-US' => JText::_('COM_JSOLR_LANG_EN_US'),
            'en-ZA' => JText::_('COM_JSOLR_LANG_EN_ZA'),
            'en-ZW' => JText::_('COM_JSOLR_LANG_EN_ZW'),
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
            'et-EE' => JText::_('COM_JSOLR_LANG_ET_EE'),
            'eu-ES' => JText::_('COM_JSOLR_LANG_EU_ES'),
            'fa-IR' => JText::_('COM_JSOLR_LANG_FA_IR'),
            'fi-FI' => JText::_('COM_JSOLR_LANG_FI_FI'),
            'fil-PH' => JText::_('COM_JSOLR_LANG_FIL_PH'),
            'fo-FO' => JText::_('COM_JSOLR_LANG_FO_FO'),
            'fr-BE' => JText::_('COM_JSOLR_LANG_FR_BE'),
            'fr-CA' => JText::_('COM_JSOLR_LANG_FR_CA'),
            'fr-CH' => JText::_('COM_JSOLR_LANG_FR_CH'),
            'fr-FR' => JText::_('COM_JSOLR_LANG_FR_FR'),
            'fr-LU' => JText::_('COM_JSOLR_LANG_FR_LU'),
            'fr-MC' => JText::_('COM_JSOLR_LANG_FR_MC'),
            'fy-NL' => JText::_('COM_JSOLR_LANG_FY_NL'),
            'ga-IE' => JText::_('COM_JSOLR_LANG_GA_IE'),
            'gd-GB' => JText::_('COM_JSOLR_LANG_GD_GB'),
            'gl-ES' => JText::_('COM_JSOLR_LANG_GL_ES'),
            'gsw-FR' => JText::_('COM_JSOLR_LANG_GSW_FR'),
            'gu-IN' => JText::_('COM_JSOLR_LANG_GU_IN'),
            'ha-Latn-NG' => JText::_('COM_JSOLR_LANG_HA_LATN_NG'),
            'he-IL' => JText::_('COM_JSOLR_LANG_HE_IL'),
            'hi-IN' => JText::_('COM_JSOLR_LANG_HI_IN'),
            'hr-BA' => JText::_('COM_JSOLR_LANG_HR_BA'),
            'hr-HR' => JText::_('COM_JSOLR_LANG_HR_HR'),
            'hsb-DE' => JText::_('COM_JSOLR_LANG_HSB_DE'),
            'hu-HU' => JText::_('COM_JSOLR_LANG_HU_HU'),
            'hy-AM' => JText::_('COM_JSOLR_LANG_HY_AM'),
            'id-ID' => JText::_('COM_JSOLR_LANG_ID_ID'),
            'ig-NG' => JText::_('COM_JSOLR_LANG_IG_NG'),
            'ii-CN' => JText::_('COM_JSOLR_LANG_II_CN'),
            'is-IS' => JText::_('COM_JSOLR_LANG_IS_IS'),
            'it-CH' => JText::_('COM_JSOLR_LANG_IT_CH'),
            'it-IT' => JText::_('COM_JSOLR_LANG_IT_IT'),
            'iu-Cans-CA' => JText::_('COM_JSOLR_LANG_IU_CANS_CA'),
            'iu-Latn-CA' => JText::_('COM_JSOLR_LANG_IU_LATN_CA'),
            'ja-JP' => JText::_('COM_JSOLR_LANG_JA_JP'),
            'ka-GE' => JText::_('COM_JSOLR_LANG_KA_GE'),
            'kk-KZ' => JText::_('COM_JSOLR_LANG_KK_KZ'),
            'kl-GL' => JText::_('COM_JSOLR_LANG_KL_GL'),
            'km-KH' => JText::_('COM_JSOLR_LANG_KM_KH'),
            'kn-IN' => JText::_('COM_JSOLR_LANG_KN_IN'),
            'kok-IN' => JText::_('COM_JSOLR_LANG_KOK_IN'),
            'ko-KR' => JText::_('COM_JSOLR_LANG_KO_KR'),
            'ky-KG' => JText::_('COM_JSOLR_LANG_KY_KG'),
            'lb-LU' => JText::_('COM_JSOLR_LANG_LB_LU'),
            'lo-LA' => JText::_('COM_JSOLR_LANG_LO_LA'),
            'lt-LT' => JText::_('COM_JSOLR_LANG_LT_LT'),
            'lv-LV' => JText::_('COM_JSOLR_LANG_LV_LV'),
            'mi-NZ' => JText::_('COM_JSOLR_LANG_MI_NZ'),
            'mk-MK' => JText::_('COM_JSOLR_LANG_MK_MK'),
            'ml-IN' => JText::_('COM_JSOLR_LANG_ML_IN'),
            'mn-MN' => JText::_('COM_JSOLR_LANG_MN_MN'),
            'mn-Mong-CN' => JText::_('COM_JSOLR_LANG_MN_MONG_CN'),
            'moh-CA' => JText::_('COM_JSOLR_LANG_MOH_CA'),
            'mr-IN' => JText::_('COM_JSOLR_LANG_MR_IN'),
            'ms-BN' => JText::_('COM_JSOLR_LANG_MS_BN'),
            'ms-MY' => JText::_('COM_JSOLR_LANG_MS_MY'),
            'mt-MT' => JText::_('COM_JSOLR_LANG_MT_MT'),
            'nb-NO' => JText::_('COM_JSOLR_LANG_NB_NO'),
            'ne-NP' => JText::_('COM_JSOLR_LANG_NE_NP'),
            'nl-BE' => JText::_('COM_JSOLR_LANG_NL_BE'),
            'nl-NL' => JText::_('COM_JSOLR_LANG_NL_NL'),
            'nn-NO' => JText::_('COM_JSOLR_LANG_NN_NO'),
            'nso-ZA' => JText::_('COM_JSOLR_LANG_NSO_ZA'),
            'oc-FR' => JText::_('COM_JSOLR_LANG_OC_FR'),
            'or-IN' => JText::_('COM_JSOLR_LANG_OR_IN'),
            'pa-IN' => JText::_('COM_JSOLR_LANG_PA_IN'),
            'pl-PL' => JText::_('COM_JSOLR_LANG_PL_PL'),
            'prs-AF' => JText::_('COM_JSOLR_LANG_PRS_AF'),
            'ps-AF' => JText::_('COM_JSOLR_LANG_PS_AF'),
            'pt-BR' => JText::_('COM_JSOLR_LANG_PT_BR'),
            'pt-PT' => JText::_('COM_JSOLR_LANG_PT_PT'),
            'qut-GT' => JText::_('COM_JSOLR_LANG_QUT_GT'),
            'quz-BO' => JText::_('COM_JSOLR_LANG_QUZ_BO'),
            'quz-EC' => JText::_('COM_JSOLR_LANG_QUZ_EC'),
            'quz-PE' => JText::_('COM_JSOLR_LANG_QUZ_PE'),
            'rm-CH' => JText::_('COM_JSOLR_LANG_RM_CH'),
            'ro-RO' => JText::_('COM_JSOLR_LANG_RO_RO'),
            'ru-RU' => JText::_('COM_JSOLR_LANG_RU_RU'),
            'rw-RW' => JText::_('COM_JSOLR_LANG_RW_RW'),
            'sah-RU' => JText::_('COM_JSOLR_LANG_SAH_RU'),
            'sa-IN' => JText::_('COM_JSOLR_LANG_SA_IN'),
            'se-FI' => JText::_('COM_JSOLR_LANG_SE_FI'),
            'se-NO' => JText::_('COM_JSOLR_LANG_SE_NO'),
            'se-SE' => JText::_('COM_JSOLR_LANG_SE_SE'),
            'si-LK' => JText::_('COM_JSOLR_LANG_SI_LK'),
            'sk-SK' => JText::_('COM_JSOLR_LANG_SK_SK'),
            'sl-SI' => JText::_('COM_JSOLR_LANG_SL_SI'),
            'sma-NO' => JText::_('COM_JSOLR_LANG_SMA_NO'),
            'sma-SE' => JText::_('COM_JSOLR_LANG_SMA_SE'),
            'smj-NO' => JText::_('COM_JSOLR_LANG_SMJ_NO'),
            'smj-SE' => JText::_('COM_JSOLR_LANG_SMJ_SE'),
            'smn-FI' => JText::_('COM_JSOLR_LANG_SMN_FI'),
            'sms-FI' => JText::_('COM_JSOLR_LANG_SMS_FI'),
            'sq-AL' => JText::_('COM_JSOLR_LANG_SQ_AL'),
            'sr-Cyrl-BA' => JText::_('COM_JSOLR_LANG_SR_CYRL_BA'),
            'sr-Cyrl-CS' => JText::_('COM_JSOLR_LANG_SR_CYRL_CS'),
            'sr-Cyrl-ME' => JText::_('COM_JSOLR_LANG_SR_CYRL_ME'),
            'sr-Cyrl-RS' => JText::_('COM_JSOLR_LANG_SR_CYRL_RS'),
            'sr-Latn-BA' => JText::_('COM_JSOLR_LANG_SR_LATN_BA'),
            'sr-Latn-CS' => JText::_('COM_JSOLR_LANG_SR_LATN_CS'),
            'sr-Latn-ME' => JText::_('COM_JSOLR_LANG_SR_LATN_ME'),
            'sr-Latn-RS' => JText::_('COM_JSOLR_LANG_SR_LATN_RS'),
            'sv-FI' => JText::_('COM_JSOLR_LANG_SV_FI'),
            'sv-SE' => JText::_('COM_JSOLR_LANG_SV_SE'),
            'sw-KE' => JText::_('COM_JSOLR_LANG_SW_KE'),
            'syr-SY' => JText::_('COM_JSOLR_LANG_SYR_SY'),
            'ta-IN' => JText::_('COM_JSOLR_LANG_TA_IN'),
            'te-IN' => JText::_('COM_JSOLR_LANG_TE_IN'),
            'tg-Cyrl-TJ' => JText::_('COM_JSOLR_LANG_TG_CYRL_TJ'),
            'th-TH' => JText::_('COM_JSOLR_LANG_TH_TH'),
            'tk-TM' => JText::_('COM_JSOLR_LANG_TK_TM'),
            'tn-ZA' => JText::_('COM_JSOLR_LANG_TN_ZA'),
            'tr-TR' => JText::_('COM_JSOLR_LANG_TR_TR'),
            'tt-RU' => JText::_('COM_JSOLR_LANG_TT_RU'),
            'tzm-Latn-DZ' => JText::_('COM_JSOLR_LANG_TZM_LATN_DZ'),
            'ug-CN' => JText::_('COM_JSOLR_LANG_UG_CN'),
            'uk-UA' => JText::_('COM_JSOLR_LANG_UK_UA'),
            'ur-PK' => JText::_('COM_JSOLR_LANG_UR_PK'),
            'uz-Cyrl-UZ' => JText::_('COM_JSOLR_LANG_UZ_CYRL_UZ'),
            'uz-Latn-UZ' => JText::_('COM_JSOLR_LANG_UZ_LATN_UZ'),
            'vi-VN' => JText::_('COM_JSOLR_LANG_VI_VN'),
            'wo-SN' => JText::_('COM_JSOLR_LANG_WO_SN'),
            'xh-ZA' => JText::_('COM_JSOLR_LANG_XH_ZA'),
            'yo-NG' => JText::_('COM_JSOLR_LANG_YO_NG'),
            'zh-CN' => JText::_('COM_JSOLR_LANG_ZH_CN'),
            'zh-HK' => JText::_('COM_JSOLR_LANG_ZH_HK'),
            'zh-MO' => JText::_('COM_JSOLR_LANG_ZH_MO'),
            'zh-SG' => JText::_('COM_JSOLR_LANG_ZH_SG'),
            'zh-TW' => JText::_('COM_JSOLR_LANG_ZH_TW'),
            'zu-ZA' => JText::_('COM_JSOLR_LANG_ZU_ZA')
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

    function fillQuery()
    {
        $filter = $this->getFilter();

        if ($filter) {
            $this->form->getQuery()->mergeFilters($filter);
            return true;
        }

        return false;
    }

    function getValueText()
    {
        if (!is_array($this->value) || count($this->value) == 0) {
            return JText::_(COM_JSOLRSEARCH_LANGUAGE_ALL);
        }

        $result = array();
        $options = $this->getFinalOptions();

        foreach ($this->value as $v) {
            $result[] = $options[$v];
        }

        return implode(', ', $result);
    }
}