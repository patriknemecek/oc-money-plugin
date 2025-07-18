<?php

declare(strict_types=1);

namespace Initbiz\Money\FormWidgets;

use Backend\Classes\FormField;
use Initbiz\Money\Classes\Helpers;
use Backend\Classes\FormWidgetBase;
use Responsiv\Currency\Models\Currency as CurrencyModel;

/**
 * Money input
 */
class Money extends FormWidgetBase
{
    /**
     * @var string Money format to display
     */
    public $format = null;

    /**
     * @var string Mode of the field (amount|amountcurrency)
     */
    public $mode = 'amountcurrency';

    /**
     * @var string mode that the widget will use to save and get value (array|json)
     */
    public $valueMode = 'array';

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
            'mode',
            'valueMode',
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
        $currency = $primaryCurrency;

        $value = $this->getLoadValue();
        if ($this->valueMode === 'json') {
            $value = json_decode($value, true);
        }

        if ($value) {
            $amount = $value['amount'];
            $currencyCode = $value['currency'];
            $currency = CurrencyModel::findByCode($currencyCode);
        } else {
            $amount = 0;
            $currencyCode = $primaryCurrency->currency_code;
        }

        $name = $this->formField->getName() . "[amount]";
        $currenciesFieldName = $this->formField->getName() . "[currency]";

        $currenciesField = new FormField($currenciesFieldName, $this->label . "_currency");
        $currenciesField->options = CurrencyModel::listEnabled();
        $currenciesField->value = $currencyCode;
        $currenciesField->attributes = $this->formField->attributes;
        $currenciesField->disabled = $this->formField->disabled;
        $currenciesField->readOnly = $this->formField->readOnly;

        $currencyConfig = $currency;

        $this->vars['name'] = $name;
        $this->vars['format'] = $this->format;
        $this->vars['amount'] = $amount;
        $this->vars['primaryCurrency'] = $primaryCurrency;
        $this->vars['currenciesField'] = $currenciesField;
        $this->vars['currencyConfig'] = $currencyConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        if ($this->formField->disabled || $this->formField->hidden) {
            return FormField::NO_SAVE_DATA;
        }

        if (is_null($value)) {
            return null;
        }

        $value['amount'] = (int) Helpers::removeNonNumeric($value['amount']);

        if ($this->valueMode === 'json') {
            $value = json_encode($value);
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addCss(['~/plugins/initbiz/money/formwidgets/money/assets/css/money.css']);
        $this->addJs([
            '~/plugins/initbiz/money/assets/node_modules/dinero.js/build/umd/dinero.min.js',
            '~/plugins/initbiz/money/assets/js/money-helpers.js',
            '~/plugins/initbiz/money/assets/js/money-manipulator.js',
            '~/plugins/initbiz/money/formwidgets/money/assets/js/config-manager.js',
            '~/plugins/initbiz/money/formwidgets/money/assets/js/money-widget.js',
            '~/plugins/initbiz/money/formwidgets/money/assets/js/money-widget-handlers.js'
        ]);
    }
}
