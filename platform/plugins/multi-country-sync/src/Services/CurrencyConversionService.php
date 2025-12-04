<?php

namespace Botble\MultiCountrySync\Services;

use Illuminate\Support\Facades\Log;

class CurrencyConversionService
{
    /**
     * Convert price from source currency to target currency
     *
     * @param float $price
     * @param string $sourceCountry (eg, uae, sa)
     * @param string $targetCountry (eg, uae, sa)
     * @return float
     */
    public function convertPrice(float $price, string $sourceCountry, string $targetCountry): float
    {
        // If same country, no conversion needed
        if ($sourceCountry === $targetCountry) {
            return $price;
        }

        // Get exchange rates from config
        $exchangeRates = config('plugins.multi-country-sync.sync.exchange_rates', []);
        
        // Get currencies for each country
        $currencies = config('plugins.multi-country-sync.sync.currencies', []);
        $sourceCurrency = $currencies[$sourceCountry] ?? 'EGP';
        $targetCurrency = $currencies[$targetCountry] ?? 'EGP';

        // Get exchange rate
        $exchangeRate = $this->getExchangeRate($sourceCountry, $targetCountry, $exchangeRates);

        if ($exchangeRate === null) {
            Log::warning('Multi-Country Sync: Exchange rate not found', [
                'source_country' => $sourceCountry,
                'target_country' => $targetCountry,
                'source_currency' => $sourceCurrency,
                'target_currency' => $targetCurrency,
                'price' => $price,
            ]);
            
            // Return original price if exchange rate not found
            return $price;
        }

        // Convert price
        $convertedPrice = $price * $exchangeRate;

        Log::debug('Multi-Country Sync: Currency conversion', [
            'source_country' => $sourceCountry,
            'target_country' => $targetCountry,
            'source_currency' => $sourceCurrency,
            'target_currency' => $targetCurrency,
            'original_price' => $price,
            'exchange_rate' => $exchangeRate,
            'converted_price' => $convertedPrice,
        ]);

        // Round to 2 decimal places
        return round($convertedPrice, 2);
    }

    /**
     * Get exchange rate between two countries
     *
     * @param string $sourceCountry
     * @param string $targetCountry
     * @param array $exchangeRates
     * @return float|null
     */
    protected function getExchangeRate(string $sourceCountry, string $targetCountry, array $exchangeRates): ?float
    {
        // Try direct rate: source -> target
        $rateKey = "{$sourceCountry}_to_{$targetCountry}";
        if (isset($exchangeRates[$rateKey])) {
            return (float) $exchangeRates[$rateKey];
        }

        // Try reverse rate: target -> source (inverse)
        $reverseRateKey = "{$targetCountry}_to_{$sourceCountry}";
        if (isset($exchangeRates[$reverseRateKey])) {
            return 1 / (float) $exchangeRates[$reverseRateKey];
        }

        // Try using base currency (EGP) as intermediary
        // If converting from SAR to AED: first convert SAR -> EGP, then EGP -> AED
        if ($sourceCountry !== 'eg' && $targetCountry !== 'eg') {
            // Both are not EGP, use EGP as base
            // First get source -> EGP rate
            $sourceToEgKey = "{$sourceCountry}_to_eg";
            $egToSourceKey = "eg_to_{$sourceCountry}";
            $egToTargetKey = "eg_to_{$targetCountry}";
            
            $sourceToEg = null;
            if (isset($exchangeRates[$sourceToEgKey])) {
                $sourceToEg = (float) $exchangeRates[$sourceToEgKey];
            } elseif (isset($exchangeRates[$egToSourceKey])) {
                // Inverse: source -> EGP = 1 / (EGP -> source)
                $sourceToEg = 1 / (float) $exchangeRates[$egToSourceKey];
            }
            
            // Get EGP -> target rate
            $egToTarget = null;
            if (isset($exchangeRates[$egToTargetKey])) {
                $egToTarget = (float) $exchangeRates[$egToTargetKey];
            } else {
                $targetToEgKey = "{$targetCountry}_to_eg";
                if (isset($exchangeRates[$targetToEgKey])) {
                    // Inverse: EGP -> target = 1 / (target -> EGP)
                    $egToTarget = 1 / (float) $exchangeRates[$targetToEgKey];
                }
            }
            
            if ($sourceToEg !== null && $egToTarget !== null) {
                return $sourceToEg * $egToTarget;
            }
        } elseif ($sourceCountry === 'eg') {
            // Converting from EGP to another currency
            $egToTargetKey = "eg_to_{$targetCountry}";
            if (isset($exchangeRates[$egToTargetKey])) {
                return (float) $exchangeRates[$egToTargetKey];
            }
            
            // Try reverse
            $targetToEgKey = "{$targetCountry}_to_eg";
            if (isset($exchangeRates[$targetToEgKey])) {
                return 1 / (float) $exchangeRates[$targetToEgKey];
            }
        } elseif ($targetCountry === 'eg') {
            // Converting to EGP from another currency
            $sourceToEgKey = "{$sourceCountry}_to_eg";
            if (isset($exchangeRates[$sourceToEgKey])) {
                return (float) $exchangeRates[$sourceToEgKey];
            }
            
            // Try reverse
            $egToSourceKey = "eg_to_{$sourceCountry}";
            if (isset($exchangeRates[$egToSourceKey])) {
                return 1 / (float) $exchangeRates[$egToSourceKey];
            }
        }

        return null;
    }

    /**
     * Convert multiple prices (price, sale_price, cost_per_item)
     *
     * @param array $data Product data array
     * @param string $sourceCountry
     * @param string $targetCountry
     * @return array
     */
    public function convertProductPrices(array $data, string $sourceCountry, string $targetCountry): array
    {
        $priceFields = ['price', 'sale_price', 'cost_per_item'];

        foreach ($priceFields as $field) {
            if (isset($data[$field]) && is_numeric($data[$field]) && $data[$field] > 0) {
                $data[$field] = $this->convertPrice(
                    (float) $data[$field],
                    $sourceCountry,
                    $targetCountry
                );
            }
        }

        return $data;
    }
}

