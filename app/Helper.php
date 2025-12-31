<?php

namespace App;

use App\Models\Municipality;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait Helper
{
    public function pagination(LengthAwarePaginator $paginated)
    {
        return [
            'total' => $paginated->total(),
            'last_page' => $paginated->lastPage(),
            'per_page' => (int)$paginated->perPage(),
            'current_page' => $paginated->currentPage(),
            'path' => \request()->path(),
        ];
    }

    public function paginateRequest($request, $query, $resource = null, $select = null, $relation = null)
    {
        $perPage     = 20;
        $currentPage = config('custom.current_page', 1);

        if (isset($request['per_page'])) {
            $perPage = (int) $request['per_page'];
        }
        if (isset($request['current_page'])) {
            $currentPage = (int) $request['current_page'];
        }

        $builder = is_string($query) ? $query::query() : $query;

        // ------------------------------------------------------------------
        if ($select) {
            $builder = $builder->select($select);
        }
        if ($relation) {
            $builder = $builder->with($relation);
        }

        $paginated = $builder->paginate($perPage, ['*'], 'page', $currentPage);

        $response = $this->pagination($paginated);

        if ($resource) {
            $response['data'] = $resource::collection($paginated);
        } else {
            $response['data'] = $paginated->getCollection();
        }

        return $response;
    }

    public function arrayPaginateRequest($request, $data)
    {
        if (empty($data)) $data = [null];
        $per_page = count($data);
        $current_page = config('custom.current_page');
        if (isset($request['current_page'])) {
            $per_page = isset($request['per_page']) ? $request['per_page'] : 20;
            if (isset($request['current_page'])) $current_page = $request['current_page'];
        }
        $chhetras = new LengthAwarePaginate(collect($data)->forPage($current_page, $per_page), count($data), $per_page, $current_page);
        $response = $this->pagination($chhetras);
        $response['data'] = is_null($chhetras->values()->last()) ? [] : $chhetras->values();
        return $response;
    }

    /**
     * Get raw OSM address from coordinates.
     */
    public function getAddressFromCoordinates(float $lat, float $lon, string $language = 'en', int $zoom = 18, int $detail = 1): ?array
    {
        // Use environment variable for email, fallback to a default
        $email = env('OSM_USER_EMAIL', 'admin@lems.com');
        $userAgent = "LEMS/1.0 ({$email})";
        
        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->withHeaders([
                    'User-Agent' => $userAgent,
                    'Accept' => 'application/json'
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lon,
                    'zoom' => $zoom,
                    'addressdetails' => $detail,
                    'accept-language' => $language
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Check if we got a valid response with address data
                if (isset($data['address']) && is_array($data['address']) && !empty($data['address'])) {
                    return $data['address'];
                }
                
                // Log if address is missing
                Log::warning('OSM API returned success but no address data', [
                    'lat' => $lat,
                    'lon' => $lon,
                    'response' => $data
                ]);
            } else {
                Log::error('OSM API request failed', [
                    'lat' => $lat,
                    'lon' => $lon,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('OSM API exception', [
                'lat' => $lat,
                'lon' => $lon,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Convert the raw OSM address array into a compact human-readable string.
     */
    public function compactAddress(array $geo): ?string
    {
        $parts = [];

        // Municipality / ward / city
        if (!empty($geo['city_district'])) {
            $parts[] = $geo['city_district'];
        } elseif (!empty($geo['city'])) {
            $parts[] = $geo['city'];
        } elseif (!empty($geo['town'])) {
            $parts[] = $geo['town'];
        } elseif (!empty($geo['village'])) {
            $parts[] = $geo['village'];
        }

        // Suburb / neighborhood
        if (!empty($geo['suburb'])) {
            $parts[] = $geo['suburb'];
        } elseif (!empty($geo['neighbourhood'])) {
            $parts[] = $geo['neighbourhood'];
        } elseif (!empty($geo['quarter'])) {
            $parts[] = $geo['quarter'];
        }

        // District / county / state
        if (!empty($geo['county'])) {
            $parts[] = $geo['county'];
        } elseif (!empty($geo['state_district'])) {
            $parts[] = $geo['state_district'];
        } elseif (!empty($geo['region'])) {
            $parts[] = $geo['region'];
        } elseif (!empty($geo['state'])) {
            $parts[] = $geo['state'];
        }

        // Country
        if (!empty($geo['country'])) {
            $parts[] = $geo['country'];
        }

        // Remove duplicates and empty values
        $parts = array_unique(array_filter($parts));

        // Return null if no parts found, otherwise return joined string
        return empty($parts) ? null : implode(', ', $parts);
    }

    /**
     * Get final location info including compact address, Google Maps URL, and city.
     */
    public function getEventLocationInfo(float $lat, float $lon): array
    {
        $addressArray = $this->getAddressFromCoordinates($lat, $lon);

        // Generate compact address from the address array
        $compact = $addressArray ? $this->compactAddress($addressArray) : null;

        // Generate Google Maps URL
        $mapUrl = "https://www.google.com/maps/place/{$lat},{$lon}/@{$lat},{$lon},17z";

        // Determine the city / municipality for accuracy
        $city = null;
        if ($addressArray) {
            // Try multiple keys to find the city name
            $cityKeys = ['city', 'town', 'village', 'city_district', 'municipality', 'county'];
            foreach ($cityKeys as $key) {
                if (!empty($addressArray[$key])) {
                    $city = $addressArray[$key];
                    break;
                }
            }
        }
        
        return [
            'map_address' => $compact,
            'map_url' => $mapUrl,
            'city' => $city,
        ];
    }

    public function getMunicipalityIdByName(?string $name)
    {
        if ($name !== null) {
            $name = explode('-', $name)[0];
            $municipalityId = Municipality::whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($name) . '%'])
                ->orderByRaw('LENGTH(name)') // shorter names first (more precise)
                ->orderByRaw('LOCATE(?, LOWER(name))', [strtolower($name)]) // closer match priority
                ->first()?->id;
            return $municipalityId;
        }
        return null;
    }

    /**
     * Generate QR code as data URI (PNG if imagick available, SVG otherwise)
     */
    public function generateQrCodeDataUri(string $data, int $size = 120): string
    {
        // Try PNG format first (will use imagick if available)
        try {
            $qrPng = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size($size)
                ->errorCorrection('H')
                ->generate($data);
            return 'data:image/png;base64,' . base64_encode($qrPng);
        } catch (\Exception $e) {
            // Fallback to SVG if PNG fails (imagick not available)
            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size($size)
                ->errorCorrection('H')
                ->generate($data);
            $qrSvg = preg_replace('/<\?xml[^>]*\?>/i', '', $qrSvg);
            $qrSvg = trim($qrSvg);
            return 'data:image/svg+xml;charset=utf-8,' . rawurlencode($qrSvg);
        }
    }
}
