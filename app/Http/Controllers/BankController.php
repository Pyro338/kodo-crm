<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bank;
use App\Services\SimpleXLSX;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view(
            'banks.index',
            [
                'banks' => Bank::paginate(50)
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view(
            'banks.create',
            [
                'bank' => []
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Bank::create($request->all());

        return redirect()->route('banks.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view(
            'banks.show',
            [
                'bank' => Bank::where('id', $id)->first()
            ]
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view(
            'banks.edit',
            [
                'bank' => Bank::where('id', $id)->first()
            ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $bank = Bank::find($id);
        $update_query = $request->all();
        $bank->update($update_query);
        if(!isset($update_query['license_status'])){
            $bank->license_status = 0;
        }
        $bank->save();

        return redirect()->route('banks.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $bank = Bank::where('id', $id)->first();
        $bank->delete();

        return redirect()->route('banks.index');
    }

    public function loadBanksIndex()
    {
        return view('banks.load');
    }

    public function loadBanks(Request $request)
    {
        Bank::truncate();
        foreach ($request->file() as $file) {
            foreach ($file as $f) {
                $filename = time() . '_' . $f->getClientOriginalName();
                if ($f->move(storage_path('app/public/files/'), $filename)) {
                    if ($xlsx = SimpleXLSX::parse('storage/files/' . $filename)) {
                        foreach ($xlsx->rows() as $row_number => $row) {
                            if ($row_number != 0 && is_int($row[0])) {
                                $bank                 = new Bank;
                                $bank->place          = $row[0];
                                $bank->reg_number     = $row[1];
                                $bank->name           = $row[2];
                                $bank->city           = $row[3];
                                $bank->place_active   = $row[4];
                                $bank->credits        = $row[5];
                                $bank->license        = $row[6];
                                if(trim($row[7]) == '+'){
                                    $bank->license_status = 1;
                                }else{
                                    $bank->license_status = 0;
                                }
                                $bank->contacts       = $row[8];
                                $bank->comments       = $row[9];
                                $bank->save();
                                var_dump($bank);
                            }
                        }
                    } else {
                        echo SimpleXLSX::parse_error();
                    }
                }
            }
        }
    }
}
