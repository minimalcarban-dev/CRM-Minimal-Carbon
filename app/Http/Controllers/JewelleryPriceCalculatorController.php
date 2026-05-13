<?php

namespace App\Http\Controllers;

use App\Models\MetalType;
use App\Models\RingSize;
use App\Models\ClosureType;
use App\Models\StoneType;
use App\Models\StoneShape;
use App\Models\StoneColor;
use App\Models\DiamondClarity;
use App\Models\DiamondCut;
use App\Services\JewelleryPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class JewelleryPriceCalculatorController extends Controller
{
    public function __construct(
        private JewelleryPricingService $pricingService
    ) {
    }

    /**
     * Display the jewellery price calculator tool.
     */
    public function index()
    {
        $data = [
            'stoneTypes' => Cache::remember('stone_types_list', 86400, function () {
                return StoneType::where('is_active', true)->orderBy('name')->get();
            }),
            'stoneShapes' => Cache::remember('stone_shapes_list', 86400, function () {
                return StoneShape::where('is_active', true)->orderBy('name')->get();
            }),
            'stoneColors' => Cache::remember('stone_colors_list', 86400, function () {
                return StoneColor::where('is_active', true)->orderBy('name')->get();
            }),
            'diamondClarities' => Cache::remember('diamond_clarities_list', 86400, function () {
                return DiamondClarity::where('is_active', true)->orderBy('name')->get();
            }),
            'diamondCuts' => Cache::remember('diamond_cuts_list', 86400, function () {
                return DiamondCut::where('is_active', true)->orderBy('name')->get();
            }),
            'pricingDefaults' => $this->pricingService->defaultsFor(auth('admin')->user()),
            'pricingRows' => $this->pricingService->formRows(),
        ];

        return view('jewellery-price-calculator.index', $data);
    }
}
