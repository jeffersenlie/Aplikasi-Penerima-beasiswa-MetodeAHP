<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PairwiseComparison;
use App\Models\SubCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function store(Request $request, SubCriteria $subCriteria)
    {
        $data = $request->validate(
            ['name' => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'name')->where('sub_criteria_id', $subCriteria->id),
            ]],
            [],
            ['name' => 'nama kategori']
        );
        $data['order'] = ($subCriteria->categories()->max('order') ?? 0) + 1;

        $subCriteria->categories()->create($data);

        return back()->with('success', "Kategori \"{$data['name']}\" ditambahkan.");
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'  => [
                'required', 'string', 'max:100',
                Rule::unique('categories', 'name')
                    ->where('sub_criteria_id', $category->sub_criteria_id)
                    ->ignore($category->id),
            ],
            'order' => ['required', 'integer', 'min:1'],
        ], [], [
            'name'  => 'nama kategori',
            'order' => 'urutan',
        ]);

        $category->update($data);

        return back()->with('success', "Kategori \"{$category->name}\" diperbarui.");
    }

    public function destroy(Category $category)
    {
        DB::transaction(function () use ($category) {
            PairwiseComparison::purgeItem('category', $category->id);
            $category->delete();
        });

        return back()->with('success', "Kategori \"{$category->name}\" dihapus.");
    }
}
