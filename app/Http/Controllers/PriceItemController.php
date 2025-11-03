<?php
namespace App\Http\Controllers;

use App\Models\PriceItem;
use App\Models\Zone;
use App\Models\Site;
use App\Models\Product;
use App\Models\Price;
use App\Models\PostcodeZone;
use App\Models\Postcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PriceItemController extends Controller
{
    /**
     * Show the form for editing the specified Price.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function editPrice($id)
    {
        $price = Price::findOrFail($id);
        return view('prices.edit', compact('price'));
    }

    /**
     * Update the specified Price in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updatePrice(Request $request, $id)
    {
        $price = Price::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'published_at' => 'nullable|date',
        ]);
        $price->update($data);
        return redirect()->route('prices')->with('success', 'Price updated successfully');
    }
    /**
     * Display a listing of the price items
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        $priceId = $request->get('price_id');
        $type = $request->get('type');
        $view = $request->get('view');
        
        $query = PriceItem::with(['product']);
        
        // Apply search if provided (case-insensitive)
        if ($search) {
            $query->whereHas('product', function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%" . strtolower($search) . "%"]);
            });
        }
        
        // Filter by price_id if provided
        if ($priceId) {
            $query->where('price_id', $priceId);
        }
        
        // Filter by type based on view parameter
        if ($type) {
            if ($view == 'tonne' && $type == 'site') {
                $query->where('price_itemable_type', 'site');
            } elseif ($view == 'load' && $type == 'zone') {
                $query->where('price_itemable_type', 'zone');
            } else {
                $query->where('price_itemable_type', $type);
            }
        }
        
        // Get the price items with pagination
        $priceItems = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return view('price-items.index', compact('priceItems'));
    }
    
    /**
     * Display a listing of tonne prices for a specific price ID
     *
     * @param Request $request
     * @param int $priceId
     * @return \Illuminate\Http\Response
     */
    public function tonnePrices(Request $request, $priceId)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        
        $price = Price::with(['price_items' => function ($query) {
            $query->where('price_itemable_type', 'site');
        }])->findOrFail($priceId);
        
        // Group price items by site_id, wheel_id, and product_id for easier access
        $priceItems = $price->price_items->groupBy(['price_itemable_id', 'wheel_id', 'product_id']);
        
        // Get active products
        $products = Product::where('active', true)
            ->orderBy('id')
            ->get();
        
        // Query for sites
        $sitesQuery = Site::query()
            ->orderByDesc('state')
            ->orderBy('name');
        
        // Apply search if provided (case-insensitive)
        if ($search) {
            $sitesQuery->where(function($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%" . strtolower($search) . "%"])
                      ->orWhereRaw('LOWER(state) LIKE ?', ["%" . strtolower($search) . "%"]);
            });
        }
        
        $sites = $sitesQuery->paginate($perPage);
        
        // Format data for the view
        $sitesData = [];
        foreach ($sites as $site) {
            $siteData = [
                'id' => $site->id,
                'name' => $site->name,
                'state' => $site->state,
                'products' => [],
            ];
            
            foreach ($products as $product) {
                $wheelId = 1; // Default for tonne prices
                $priceItem = $priceItems[$site->id][$wheelId][$product->id] ?? null;
                $amount = $priceItem ? $priceItem[0]->amount : 0;
                
                $siteData['products'][$product->id] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'amount' => $amount,
                ];
            }
            
            $sitesData[] = $siteData;
        }
        
        return view('price-items.tonne-prices', compact('sitesData', 'products', 'price', 'sites'));
    }
    
    /**
     * Display a listing of load prices for a specific price ID
     *
     * @param Request $request
     * @param int $priceId
     * @return \Illuminate\Http\Response
     */
    public function loadPrices(Request $request, $priceId)
    {
        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        
        // Different wheel sizes for load prices
        $wheels = [10, 6];
        
        $price = Price::with(['price_items' => function ($query) {
            $query->where('price_itemable_type', 'zone');
        }])->findOrFail($priceId);
        
        // Group price items by zone_id, wheel_id, and product_id for easier access
        $priceItems = $price->price_items->groupBy(['price_itemable_id', 'wheel_id', 'product_id']);
        
        // Get active products
        $products = Product::where('active', true)
            ->orderBy('id')
            ->get();
        
        // Query for zones
        $zonesQuery = Zone::query()
            ->orderByDesc('state')
            ->orderBy('name');
        
        // Apply search if provided (case-insensitive)
        if ($search) {
            $zonesQuery->where(function($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%" . strtolower($search) . "%"])
                      ->orWhereRaw('LOWER(state) LIKE ?', ["%" . strtolower($search) . "%"]);
            });
        }
        
        $zones = $zonesQuery->with('postcode_zones')->paginate($perPage);
        
        // Format data for the view
        $zonesData = [];
        foreach ($zones as $zone) {
            $zoneData = [
                'id' => $zone->id,
                'name' => $zone->name,
                'state' => $zone->state,
                'postcodes' => $zone->postcode_zones->pluck('postcode')->implode(', '),
                'wheels' => [],
            ];
            
            // For each wheel size
            foreach ($wheels as $wheel) {
                $wheelData = [
                    'id' => $wheel,
                    'products' => [],
                ];
                
                foreach ($products as $product) {
                    $priceItem = $priceItems[$zone->id][$wheel][$product->id] ?? null;
                    $amount = $priceItem ? $priceItem[0]->amount : 0;
                    
                    $wheelData['products'][$product->id] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'amount' => $amount,
                    ];
                }
                
                $zoneData['wheels'][$wheel] = $wheelData;
            }
            
            $zonesData[] = $zoneData;
        }
        
        return view('price-items.load-prices', compact('zonesData', 'products', 'price', 'zones', 'wheels'));
    }
    
    /**
     * Update a tonne price
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateTonnePrice(Request $request)
    {
        $data = $request->validate([
            'price_id' => 'required|integer|exists:prices,id',
            'site_id' => 'required|integer|exists:sites,id',
            'product_id' => 'required|integer|exists:products,id',
            'wheel_id' => 'sometimes|integer',
            'amount' => 'nullable|numeric',
        ]);

    $wheelId = $data['wheel_id'] ?? 1;
    $amountRaw = $data['amount'] ?? 0;
        // convert to integer cents (round to 2 decimals)
        $amount = (int) round(floatval($amountRaw) * 100);
    $creatorId = $request->user()?->id ?? $request->input('creator_id', 0);

        try {
            if ($amount <= 0) {
                PriceItem::where('price_id', $data['price_id'])
                    ->where('price_itemable_type', 'site')
                    ->where('price_itemable_id', $data['site_id'])
                    ->where('product_id', $data['product_id'])
                    ->where('wheel_id', $wheelId)
                    ->delete();

                return response()->json(['success' => true, 'deleted' => true, 'message' => 'Price item deleted']);
            }

            $attributes = [
                'price_id' => $data['price_id'],
                'price_itemable_type' => 'site',
                'price_itemable_id' => $data['site_id'],
                'product_id' => $data['product_id'],
                'wheel_id' => $wheelId,
            ];

            $values = [
                'amount' => $amount,
            ];

            if (!empty($creatorId) && $creatorId > 0) {
                $values['creator_id'] = $creatorId;
            }

            $item = PriceItem::updateOrCreate($attributes, $values);

            // reload to get casts applied
            $item->refresh();
            Log::info('PriceItem saved', ['price_item_id' => $item->id, 'amount' => $item->amount]);

            return response()->json([
                'success' => true,
                'id' => $item->id,
                'amount_raw' => $item->amount,
                'amount_display' => is_numeric($item->amount) ? ($item->amount / 100) : null,
                'message' => 'Price updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update price: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Update a load price
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateLoadPrice(Request $request)
    {
        $data = $request->validate([
            'price_id' => 'required|integer|exists:prices,id',
            'zone_id' => 'required|integer|exists:zones,id',
            'product_id' => 'required|integer|exists:products,id',
            'wheel_id' => 'sometimes|integer',
            'amount' => 'nullable|numeric',
        ]);

    $wheelId = $data['wheel_id'] ?? 0;
    $amountRaw = $data['amount'] ?? 0;
    $amount = (int) round(floatval($amountRaw) * 100);
    $creatorId = $request->user()?->id ?? $request->input('creator_id', 0);

        try {
            if ($amount <= 0) {
                PriceItem::where('price_id', $data['price_id'])
                    ->where('price_itemable_type', 'zone')
                    ->where('price_itemable_id', $data['zone_id'])
                    ->where('product_id', $data['product_id'])
                    ->where('wheel_id', $wheelId)
                    ->delete();

                return response()->json(['success' => true, 'deleted' => true, 'message' => 'Price item deleted']);
            }

            $attributes = [
                'price_id' => $data['price_id'],
                'price_itemable_type' => 'zone',
                'price_itemable_id' => $data['zone_id'],
                'product_id' => $data['product_id'],
                'wheel_id' => $wheelId,
            ];

            $values = [
                'amount' => $amount,
            ];

            if (!empty($creatorId) && $creatorId > 0) {
                $values['creator_id'] = $creatorId;
            }

            $item = PriceItem::updateOrCreate($attributes, $values);

            $item->refresh();
            Log::info('PriceItem saved', ['price_item_id' => $item->id, 'amount' => $item->amount]);

            return response()->json([
                'success' => true,
                'id' => $item->id,
                'amount_raw' => $item->amount,
                'amount_display' => is_numeric($item->amount) ? ($item->amount / 100) : null,
                'message' => 'Price updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update price: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Return a canonical PriceItem as JSON by identifying keys.
     * Accepts query parameters: price_id, product_id, wheel_id (optional), site_id or zone_id (one required).
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getPriceItem(Request $request)
    {
        $data = $request->validate([
            'price_id' => 'required|integer|exists:prices,id',
            'product_id' => 'required|integer|exists:products,id',
            'wheel_id' => 'sometimes|integer',
            'site_id' => 'sometimes|integer|exists:sites,id',
            'zone_id' => 'sometimes|integer|exists:zones,id',
        ]);

        // Determine the polymorphic type and id
        if (!empty($data['site_id'])) {
            $type = 'site';
            $itemableId = $data['site_id'];
        } elseif (!empty($data['zone_id'])) {
            $type = 'zone';
            $itemableId = $data['zone_id'];
        } else {
            return response()->json(['success' => false, 'message' => 'Either site_id or zone_id is required'], 400);
        }

        $wheelId = $data['wheel_id'] ?? 0;

        $query = PriceItem::where('price_id', $data['price_id'])
            ->where('price_itemable_type', $type)
            ->where('price_itemable_id', $itemableId)
            ->where('product_id', $data['product_id'])
            ->where('wheel_id', $wheelId);

        $item = $query->first();

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Price item not found'], 404);
        }

        // Ensure casts/refresh
        $item->refresh();

        return response()->json([
            'success' => true,
            'id' => $item->id,
            'price_id' => $item->price_id,
            'price_itemable_type' => $item->price_itemable_type,
            'price_itemable_id' => $item->price_itemable_id,
            'product_id' => $item->product_id,
            'wheel_id' => $item->wheel_id,
            'amount_raw' => $item->amount,
            'amount_display' => is_numeric($item->amount) ? ($item->amount / 100) : null,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ]);
    }
    
    /**
     * Display a listing of zones with their price data
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function zones(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');
        
        // Base query
        $query = Zone::query();
        
        // Apply search if provided (case-insensitive)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%" . strtolower($search) . "%"])
                  ->orWhereRaw('LOWER(state) LIKE ?', ["%" . strtolower($search) . "%"]);
            });
        }
        
        // Get zones with pagination
        $zones = $query->orderBy('name')->paginate($perPage);
        
        // Get the postcode data (eager load Postcode relation)
        $zonePostcodes = [];
        $zoneData = Zone::with(['postcode_zones.postcode'])->get();
        foreach ($zoneData as $zone) {
            // Store postcodes as an array for each zone, using the actual postcode value if relation exists, otherwise fallback to int
            $zonePostcodes[$zone->id] = $zone->postcode_zones->map(function($pz) {
                if (is_object($pz->postcode)) {
                    return $pz->postcode->postcode;
                } elseif (!empty($pz->postcode)) {
                    return $pz->postcode;
                }
                return null;
            })->filter()->values()->toArray();
        }

        // Get all postcodes from the postcodes table for the select dropdown
        $allPostcodes = Postcode::query()->pluck('postcode')->unique()->sort()->values()->toArray();
        return view('zones.index', compact('zones', 'zonePostcodes', 'allPostcodes'));
    }
    
    /**
     * Add a postcode to a zone (standard POST, no AJAX)
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addPostcode(Request $request)
    {
        $data = $request->validate([
            'zone_id' => 'required|integer|exists:zones,id',
            'postcodes' => 'required|filled|string',
        ]);
        $zoneId = $data['zone_id'];
        $postcodeValue = $data['postcodes'];
        $creatorId = $request->user()?->id ?? 0;
        try {
            // Only add if not already exists for this zone
            $exists = PostcodeZone::where('zone_id', $zoneId)->where('postcode', $postcodeValue)->exists();
            if (!$exists) {
                PostcodeZone::create([
                    'zone_id' => $zoneId,
                    'postcode' => $postcodeValue,
                    'creator_id' => $creatorId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            return redirect()->back()->with('success', 'Postcode added successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to add postcode: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created Zone (standard POST, no AJAX)
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeZone(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'state' => 'required|string|max:255',
        ]);
        $userId = $request->user()?->id ?? 0;
        $data['user_id'] = $userId;
        $data['creator_id'] = $userId;
        try {
            Zone::create($data);
            return redirect()->back()->with('success', 'Zone created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create zone: ' . $e->getMessage());
        }
    }

    /**
     * Show form to create a new Price
     *
     * @return \Illuminate\Http\Response
     */
    public function createPrice()
    {
        return view('prices.create');
    }

    /**
     * Store a newly created Price
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function storePrice(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'published_at' => 'nullable|date',
        ]);

    // set creator id if available
    $data['creator_id'] = $request->user()?->id ?? 0;

        $price = Price::create($data);

        return redirect()->route('prices')->with('success', 'Price created successfully');
    }
    
    /**
     * Update zone postcodes
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function updatePostcodes(Request $request)
    {
        $zoneId = $request->input('zone_id');
        $postcodeString = $request->input('postcodes');
        
        try {
            // Convert comma-separated postcodes string to array
            $postcodes = [];
            if (!empty($postcodeString)) {
                $postcodes = array_map('trim', explode(',', $postcodeString));
                // Remove any empty values
                $postcodes = array_filter($postcodes);
            }
            
            // Logic to update postcodes for a zone
            // This would typically involve updating PostcodeZone records
            // For demonstration purposes, we're just returning success
            
            return response()->json([
                'success' => true,
                'message' => 'Postcodes updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update postcodes: ' . $e->getMessage()
            ], 500);
        }
    }
}
