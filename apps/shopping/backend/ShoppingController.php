<?php

namespace App\Http\Controllers;

use App\Models\shopping\ShoppingClass;
use App\Models\shopping\ShoppingItem;
use App\Models\shopping\ShoppingPurchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShoppingController extends Controller
{
    public function __construct() {
        // $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() { }
    public function getShoppingDates() {
        $dates = ShoppingPurchase::where('status', 'A')->select('date as value', 'date as label')->orderBy('date', 'desc')->distinct()->get();
        Log::info(Collect($dates));
        return ['lst' => $dates, 'status' => "OK"];
    }
    public function getAllItems() {
        $itemOpts = ShoppingItem::where([['status', 'A'], ['class', '!=', null]])->select('id as value', DB::raw('CONCAT(class," ~ ",name) as label'))->orderBy('class', 'asc')->get();
        return ['lst' => $itemOpts, 'status' => "OK"];
    }
    public function getPurchasedDate($itemId) { // Log::info($itemId);
        $date = ShoppingPurchase::where([ ['status', 'A'], ['item_id', $itemId] ])->max('date'); //Log::info("Last Purchase Date $itemId $date");
        if ($date != null) return $date;
        else return "neverBoughtThisItem"; date('Y-m-d');  // no purchased date for this item
    }
    public function getShoppingList() { Log::info(['getShoppingList']);
        $docdir = config('constants.DOC_DIR'); Log::info("docdir=$docdir");
        $sdocArr = scandir($docdir . "/shopping");
        $attached = array_diff($sdocArr, array('.', '..')); //Log::info($attached);
        $itemList = DB::select('CALL get_shopping_list()'); //Log::info($itemList);
        $itemClasses = ShoppingClass::where('status', 'A')->select('id as value', 'class as label')->orderBy('id', 'desc')->get();
        // foreach($itemList as $m) $m->name = $m->name->strtoupper();
        return ['itemList' => $itemList, 'itemClasses' => $itemClasses, 'attached' => $attached, 'status' => "OK"];
    }
    public function getThisDatePurchases($date) { // Log::info("getThisDatePurchases input date  (today) $date");
        $todaysNumPurchases = ShoppingPurchase::where([ ['date', $date], ['status', 'A'] ])->count('*');
        if ($todaysNumPurchases == 0) $date = ShoppingPurchase::where('status', 'A')->max('date');
        // Log::info("getThisDatePurchases Last purchased date $date");
        $signedItems = ShoppingPurchase::where([ ['shopping_purchases.status', 'A'], ['date', $date], ['payees.status', 'A'] ])
            ->join('payees', 'payees.id', 'shopping_purchases.payee_id')
            ->select('payee_id', 'shopping_purchases.id', 'date', 'item_id', 'payee_id', 'shopping_purchases.name',
            'price', 'units', 'uni', 'tax', 'disct', 'costs', 'shopping_purchases.status', 'payees.name as payee')
            ->orderBy('payee_id')
            ->orderBy('shopping_purchases.created_at')->get();
        $itemcnt = str_pad(count($signedItems), 2, ' ', STR_PAD_LEFT);
        $unsignedItems = ShoppingPurchase::where([ ['status', 'A'], ['date', $date], ['payee_id', null] ])
            // ->select('id', 'date', 'item_id', 'payee_id', 'name', 'price', 'units', 'uni', 'tax', 'disct', 'costs', 'status', DB::raw("'unsigned' as payee"))
            ->select('id', 'date', 'item_id', 'payee_id', 'name', 'price', 'units', 'uni', 'tax', 'disct', 'costs', 'status')
            ->orderBy('created_at')->get();
        $itemcnt = str_pad(count($unsignedItems), 2, ' ', STR_PAD_LEFT);
        // Log::info("getThisDatePurchases store-unsign purchases: $itemcnt items on $date");
        $items = $itemcnt > 0 ? $signedItems->merge($unsignedItems) : $signedItems;
        $itemcnt = str_pad(count($items), 2, ' ', STR_PAD_LEFT);
        // Log::info("getThisDatePurchases Total(merge) purchases: $itemcnt items on $date", [$items]);

        $opts = []; $stores = []; $idx = 0;
        foreach($items as $it) {
            $idx++;
            $pstr = str_pad($idx, 2, ' ', STR_PAD_LEFT);
            // Log::info("item $pstr: " . $it->payee . ' ~ '. $it->name);
            $opt = ['label' => $it->payee, 'value' => $it->payee_id];
            if (in_array($opt, $opts)) continue;
            array_push($opts, $opt);
            array_push($stores, $it->payee_id);
        }
        // Log::info("getThisDatePurchases items", $items->toArray());
        return ['theDate' => $date, 'lst' => $items, 'opts' => $opts, 'stores' => $stores, 'status' => "OK"];
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created shopping item to shopping_purchases.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delPurchasedItem($purchasedId) { Log::info(['CALL delPurchasedItem with purchasedId', $purchasedId]);
        $da = ShoppingPurchase::find($purchasedId);
        try {
            $da->delete();
            return ['status' => 'OK'];
        } catch(Exception $ex) {
            Log::info(['FAILED CALL delPurchasedItem',$purchasedId]);
            return $ex->getMessage();
        }
    }
    public function addPurchasedItem(Request $request) { Log::info(['CALL addPurchasedItem',$request->toArray()]);
        $id = $request->id;
        if (is_null($id)) $da = new ShoppingPurchase;
        else $da = ShoppingPurchase::find($id);
        $da->date = $request->date;
        $da->status = 'A';
        $da->item_id = $request->item_id;
        $da->name = $request->name;
        $da->price = $request->price;
        $da->units = $request->units;
        $da->uni = $request->uni;
        $da->tax = $request->tax;
        $da->disct = $request->disct;
        $da->costs = $request->costs;
        try {
            $da->save();
            // $itemList = DB::select('CALL get_shopping_list()'); //Log::info($itemList);
            // return $itemList;
            // return $da;
            // return ['addedItem' => $da, 'status' => "OK" ];
            return ['status' => "OK" ];
            // return $this->getThisDatePurchases($da->date);
        } catch(Exception $ex) {
            Log::info(['FAILED CALL addPurchasedItem',$da->toArray()]);
            return $ex->getMessage();
        }
    }
    public function delShoppingItem(Request $request) { Log::info("delShoppingPurchase/Item", $request->toArray());
        if ($request->id > 0) {
            $sp = ShoppingPurchase::find($request->id);
            try {
                $sp->delete();
                // $itemList = DB::select('CALL get_shopping_list()'); //Log::info($itemList);
            } catch(Exception $ex) {
                Log::info(['FAILED CALL delShoppingItem', $sh->toArray()]);
                return $ex->getMessage();
            }
        }
        if ($request->item_id > 0) {
            $si = ShoppingItem::find($request->item_id);
            try {
                $si->delete();
                // $itemList = DB::select('CALL get_shopping_list()'); //Log::info($itemList);
            } catch(Exception $ex) {
                Log::info(['FAILED CALL delShoppingItem', $si->toArray()]);
                return $ex->getMessage();
            }
        }
        return ['status' => "OK"];
    }
    public function addShoppingItem(Request $request) {
        $da = new ShoppingPurchase($request->toArray()); Log::info(['CALL addShoppingItem',$da->toArray()]);
        try {
            $da->save();
            $itemList = DB::select('CALL get_shopping_list()'); //Log::info($itemList);
            return ['itemList' => $itemList, 'status' => "OK"];
        } catch(Exception $ex) {
            Log::info(['FAILED CALL addShoppingItem',$da->toArray()]);
            return $ex->getMessage();
        }
    }
    public function addNewItem(Request $newItem) { Log::info("addNewItem", $newItem->toArray());
        $item = ShoppingItem::where([ ['status', 'A'], ['name', $newItem->name], ['class', $newItem->class], ['class_id', $newItem->class_id] ])
            ->select('name', 'class_id', 'class')->get();
        if (count($item) > 0) return ['itemName' => $item[0]->name, 'class' => $item[0]->class, 'status' => "itemExists"];

        $dn = new ShoppingItem($newItem->toArray());
        try {
            $dn->save();
            $newItem = $dn;
            $newItem->item_id = $dn->id;
            $newItem->id = 0;
            unset($newItem->created_at);
            unset($newItem->updated_at);

            // $newItem = ['item_id' => $dn->id, 'class_id' => $dn->class_id, 'class' => $dn->class];
            return ['newItem' => $newItem, 'status' => "OK" ];
        } catch(\Exception $ex) {
            return $ex->getMessage();
        }
    }
    // public function addNewItem(Request $newItem) { Log::info("addNewItem", $newItem->toArray());
    //     $item = ShoppingItem::where([ ['status', 'A'], ['name', $newItem->name], ['class', $newItem->class], ['class_id', $newItem->class_id] ])
    //         ->select('name', 'class_id', 'class')->get();
    //     if (count($item) > 0) return ['itemName' => $item[0]->name, 'class' => $item[0]->class, 'status' => "itemExists"];

    //     $da = new ShoppingItem($newItem->toArray());
    //     try {
    //         $da->save();
    //         $itemList = DB::select('CALL get_shopping_list()'); //Log::info($itemList);
    //         $itemClasses = ShoppingClass::where('status', 'A')->select('id as value', 'class as label')->orderBy('id', 'desc')->get();
    //         $item_id = $da->id;
    //         $id = 0;
    //         foreach($itemList as $item) {
    //             if ($item->item_id == $item_id) {
    //                 $id = $item->id;
    //                 break;
    //             }
    //         }
    //         return ['itemList' => $itemList, 'itemClasses' => $itemClasses, 'item_id' => $item_id, 'id' => $id, 'status' => "OK" ];
    //     } catch(\Exception $ex) {
    //         return $ex->getMessage();
    //     }
    // }
    public function addNewClass(Request $newClass) { Log::info("addNewClass", $newClass->toArray());
        $className = ShoppingClass::where([['status', "A"], ['class', "$newClass->class"]])->value('class');
        if (strlen($className) > 0) return ['className' => $className, 'status' => "classExists"];
        $da = new ShoppingClass($newClass->toArray());
        try {
            $da->save();
            $newClass = ShoppingClass::where('status', 'A')->select('id as value', 'class as label')->orderBy('id', 'desc')->get();
            return ['newClass' => $newClass, 'status' => "OK"];
        } catch(Exception $ex) {
            return $ex->getMessage();
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $tag = $request->tag;
        if ($tag == 'newClass') {
            $da = new ShoppingClass();
            $da->class = $request->className;
            try {
                $da->save();
                return ShoppingClass::where('status', 'A')->select('id as value', 'class as label')->get();
            } catch(Exception $ex) {
                return $ex->getMessage();
            }
        } else if ($tag == 'newItem') {
            $da = new ShoppingItem();
            $da->class = $request->class;
            $da->name = $request->name;
            $da->class_id = $request->classId;
            try {
                $da->save();
                // Log::info($request);
                // return DB::select('CALL get_shopping_items(?)', [$request->date]);
                return $this->getPurchasedItemsForTheDate($request->date);
            } catch(Exception $ex) {
                return $ex->getMessage();
            }
        } else if ($tag == 'buy') {
            if ($request->status == 'D') {
                $id = $request->id;
                $da = ShoppingPurchase::find($id);
                $da->delete();
                return $da;
            } else {
                return $this->saveToDB($request);
            }
        }
    }
    private function saveToDB($dx) {
        $da = null;
        if ($dx->id > 0) {
            $da = ShoppingPurchase::find($dx->id);
            $act = "upd";
        } else {
            // $dd = ShoppingPurchase::where([['status', 'A'], ['date', $dx->date], ['item_id', $dx->item_id]])->select('id');
            // if (count($dd) == 1) {
            //     $da = ShoppingPurchase::find($dd[0]->id);
            // } else {
            //     $act = "add";
            //     $da = new ShoppingPurchase();
            // }
            $act = "add";
            $da = new ShoppingPurchase();
        }
        $da->date = $dx->date;
        $da->name = $dx->name;
        $da->item_id = $dx->itemId;
        $da->price = $dx->price;
        $da->units = $dx->units;
        $da->costs = $dx->costs;
        $da->payee_id = $dx->payee_id;
        $da->status = $dx->status;
        try {
            if ($act == "add") {
                $dp = Collect(DB::select('CALL get_last_purchased(?)', [$da->item_id]));
                if (count($dp) == 1) {
                    $dm = $dp[0];
                    $da->price = $dm->price;
                    $da->units = $dm->units;
                    $da->costs = $dm->costs;
                    // if ($dx->date == $dp->date) $da->update();
                    $da->save();
                    // $dp->id = $da->id;
                    // return $dp;
                    return $da;
                } else {
                    $da->save();
                    return $da;
                }
            } else {
                $da->update();
                return $da;
            }
            // return $da->id;
        } catch(Exceptions $e) {
            return $e->getMessage();
        }
        return $da->id;
    }

    public function XXxxshow($shoppingDate) {
        // if ($shoppingDate == 'list') $shoppingDate = null;
        $itemList = DB::select('CALL get_shopping_items(?)', [$shoppingDate]);
        $itemClasses = ShoppingClass::where('status', 'A')->select('id as value', 'class as label')->orderBy('id', 'desc')->get();
        return ['itemList' => $itemList, 'itemClasses' => $itemClasses];
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function edit(Shop $shop)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shop $shop)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shop $shop)
    {
        //
    }
}
