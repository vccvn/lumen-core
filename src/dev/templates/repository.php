<?php

namespace App\Repositories\FOLDER;

use Gomee\Repositories\BaseRepository;
use App\Masks\FOLDER\MODELMask;
use App\Masks\FOLDER\MODELCollection;
use App\Models\MODEL;
use App\Validators\FOLDER\NAMEValidator;
use Illuminate\Http\Request;

/**
 * @method MODELCollection<MODELMask>|MODEL[] filter(Request $request, array $args = []) lấy danh sách MODEL được gán Mask
 * @method MODELCollection<MODELMask>|MODEL[] getFilter(Request $request, array $args = []) lấy danh sách MODEL được gán Mask
 * @method MODELCollection<MODELMask>|MODEL[] getResults(Request $request, array $args = []) lấy danh sách MODEL được gán Mask
 * @method MODELCollection<MODELMask>|MODEL[] getData(array $args = []) lấy danh sách MODEL được gán Mask
 * @method MODELCollection<MODELMask>|MODEL[] get(array $args = []) lấy danh sách MODEL
 * @method MODELCollection<MODELMask>|MODEL[] getBy(string $column, mixed $value) lấy danh sách MODEL
 * @method MODELMask|MODEL getDetail(array $args = []) lấy MODEL được gán Mask
 * @method MODELMask|MODEL detail(array $args = []) lấy MODEL được gán Mask
 * @method MODELMask|MODEL find(integer $id) lấy MODEL
 * @method MODELMask|MODEL findBy(string $column, mixed $value) lấy MODEL
 * @method MODELMask|MODEL first(string $column, mixed $value) lấy MODEL
 * @method MODEL create(array $data = []) Thêm bản ghi
 * @method MODEL update(integer $id, array $data = []) Cập nhật
 * 
 * @property MODEL $_model Model 
 */
class NAMERepository extends BaseRepository
{
    /**
     * class chứ các phương thức để validate dử liệu
     * @var string $validatorClass 
     */
    protected $validatorClass = NAMEValidator::class;
    /**
     * tên class mặt nạ. Thường có tiền tố [tên thư mục] + \ vá hậu tố Mask
     *
     * @var string
     */
    protected $maskClass = MODELMask::class;

    /**
     * tên collection mặt nạ
     *
     * @var string
     */
    protected $maskCollectionClass = MODELCollection::class;


    /**
     * get model
     * @return string
     */
    public function getModel()
    {
        return \App\Models\MODEL::class;
    }

}