<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\Api\VerseResource;
use App\Models\TodayVerse;
use App\Models\Verse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class VerseController extends Controller
{
    //名句列表
    public function index(Request $request)
    {
        $q = Verse::query()
            ->join('poets', 'poets.id', '=', 'verses.poet_id');
        $name = $request->name;
        if ($name) {
            $q->where(function ($query) use ($name) {
                $query->where('verses.content', 'like', '%' . $name . '%')
                    ->orWhere('verses.poet_name', 'like', $name . '%')
                    ->orWhere('verses.poetry_name', 'like', $name . '%');
            });
        }
        $q->where('status', Verse::STATUS_NORMAL);
        $verses = $q ->orderBy('verses.star', 'desc')
            ->select([
                'verses.id',
                'verses.content',
                'verses.poet_name',
                'verses.poetry_id',
                'verses.poetry_name',
                'verses.star',
                'poets.dynasty_name'
            ])
            ->paginate(10);
        return $this->success($verses);
    }
    // 今日名句
    public function today(Request $request)
    {
        $date = $request->date;
        Validator::make([$date], [
            'date' => 'sometimes|date|before_or_equal:today',
        ])->validate();

        if (!$date) {
            $date = Carbon::today()->toDateString();
        }
        $today =  TodayVerse::query()->where('time', $date)->first();
        if (!$today) {
            return $this->success([]);
        }
        $verse = Verse::query()->where('id', $today->verse_id)->firstOrFail();
        return $this->success(new VerseResource($verse));
    }
}
