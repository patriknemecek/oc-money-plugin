<?php namespace Initbiz\Money\FormWidgets;

use Html;
use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;
use Responsiv\Currency\Models\Currency as CurrencyModel;
use RainLab\Location\Models\Setting;

/**
 * Money input
 */
class Money extends FormWidgetBase
{
    /**
     * @var string Money format to display (long|short)
     */
    public $format = null;

    /**
     * {@inheritDoc}
     */
    public $defaultAlias = 'money';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $this->fillFromConfig([
            'format',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('money');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $primaryCurrency = CurrencyModel::getPrimary();

        $value = $this->getLoadValue();

        if ($value) {
            $amount = $this->getLoadValue()->amount;
            $currencyCode = $this->getLoadValue()->currency;
        } else {
            $amount = 0;
            $currencyCode = $primaryCurrency->currency_code;
        }

        $name = $this->formField->getName()."[amount]";
        $currenciesFieldName = $this->formField->getName()."[currency]";

        $currenciesField = new FormField($currenciesFieldName, $this->label."_currency");
        $currenciesField->options = CurrencyModel::listEnabled();
        $currenciesField->value = $currencyCode;

        $this->vars['name'] = $name;
        $this->vars['format'] = $this->format;
        $this->vars['amount'] = $amount;
        $this->vars['primaryCurrency'] = $primaryCurrency;
        $this->vars['currenciesField'] = $currenciesField;
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        if ($this->formField->disabled || $this->formField->hidden) {
            return FormField::NO_SAVE_DATA;
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
    }
}
