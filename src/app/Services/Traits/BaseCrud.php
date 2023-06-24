<?php

namespace Gomee\Services\Traits;

use Gomee\Helpers\Arr;
use Illuminate\Http\Request;
// use Gomee\Html\HTML;


use Gomee\Laravel\Router;


/**
 * các thuộc tính và phương thức của form sẽ được triển trong ManagerController
 * @method mixed ajaxSaveSuccess(Request $request, \Gomee\Models\Model $model) can thiệp sau khi lưu ajax thành công
 * @method mixed ajaxSaveError(Request $request, array $errors) can thiệp Khi có lỗi xảy ra
 * 
 */
trait BaseCrud
{
    public $createMessage = null;
    public $updateMessage = null;
    public $handleMessage = null;

    /**
     * @var \Gomee\Validators\Validator
     */
    public $validator;

    protected $redirectData = [];

    /**
     * xử lý dữ liệu
     * @param Request $request
     * 
     * @return mixed
     */
    public function handle(Request $request)
    {
        $this->callCrudEvent('beforeHandleValidate', $request);
        // validate
        $this->fire('beforeHandleValidate', $this, $request);

        $validator = $this->repository->validator($request);

        if (!$validator->success()) {
            $errors = $validator->errors();
            if ($rs = $this->callCrudEvent('onError', $request, $errors, $validator)) {
                return $rs;
            }
            if ($rs = $this->fire('handleFailed', $this, $request, $errors)) {
                foreach ($rs as $r) {
                    if ($r) return $r;
                }
            }
            return redirect()->back()->withErrors($validator->getErrorObject())->withInput();
        }

        // tao doi tuong data de de truy cap phan tu
        $data = new Arr($validator->inputs());

        if ($res = $this->callCrudEvent('done', $request, $data)) {
            return $res;
        }
        if ($rs = $this->fire('done', $this, $request, $data)) {
            foreach ($rs as $r) {
                if ($r) return $r;
            }
        }

        $redirect = $this->redirectAfterHandle();

        return $redirect->with('success', $this->handleMessage?$this->handleMessage: "Thao tác thành công");
    }


    public function redirectAfterHandle()
    {
        if ($this->redirectRoute && Router::getByName($route = $this->routeNamePrefix . $this->redirectRoute)) {
            if (is_array($this->redirectRouteParams) && count($this->redirectRouteParams)) {
                $redirect = redirect()->route($route, $this->redirectRouteParams);
            } else {
                $redirect = redirect()->route($route);
            }
        } else {
            $redirect = redirect()->back()->withInput();
        }
        return $redirect;
    }



    /**
     * lưu thông tin bằng ajax
     *
     * @param Request $request
     * @param string $action chỉ định tạo mới hoặc update
     * @return void
     */
    public function save(Request $request, $action = null)
    {
        extract($this->apiDefaultData);
        $id = strtolower($action) != 'create' ? $request->id : null;
        // kiểm tra sự tồn tại của bản ghi qua id
        $result = null;
        if ($id && !($result = $this->repository->find($id))) {
            $message = 'Thiếu thông tin';

        } else {
            $action = $id ? 'Update' : 'Create';
            // gọi phuong thuc bat su kien
            $act = strtolower($action);
            $this->callCrudEvent('before' . $action . 'Validate', $request, $id);
            $this->callCrudEvent('beforeValidate', $request, $id);
            $this->fire('before' . $action . 'Validate', $this, $request, $id);
            $this->fire('beforeSaveValidate', $this, $request, $id);

            $validator = $this->repository->validator($request);

            if (!$validator->success()) {
                $errs = $validator->errors();
                $errors = [];
                foreach ($errs as $key => $value) {
                    $errors[] = [
                        'key' => $key,
                        'message' => $value
                    ];
                }
                if ($rs = $this->callCrudEvent('saveError', $request, $errors)) {
                    return $rs;
                }
                if ($rs = $this->fire('saveFailed', $this, $request, $errors)) {
                    foreach ($rs as $r) {
                        if ($r) return $r;
                    }
                }
                $message = 'Thông tin không hợp lệ';
            } else {
                // lấy dữ liệu sau khi dược xử lý và validate
                $arrInput = new Arr($validator->inputs());

                // xử lý dữ liệu

                $callEventData = $this->callCrudEvent('before' . $action, $request, $arrInput, $result);
                if($callEventData && $callEventData !== true) return $callEventData;
                $callEventData = $this->callCrudEvent('beforeSave', $request, $arrInput, $result);
                if($callEventData && $callEventData !== true) return $callEventData;
                $callEventData = $this->fire($act .'ing', $this, $request, $arrInput, $result);
                if($callEventData && $callEventData !== true) return $callEventData;
                $callEventData = $this->fire('saving', $this, $request, $arrInput, $result);
                if($callEventData && $callEventData !== true) return $callEventData;
                
                // lấy dữ liệu đã qua xử lý
                $inputs = $arrInput->all();

                // nếu có data và lưu thành công
                if ($inputs && $model = $this->repository->save($inputs, $id)) {
                    // thao tac sau khi luu tru
                    $callEventData = $this->callCrudEvent('after' . $act, $request, $model, $arrInput);
                    if($callEventData && $callEventData !== true) return $callEventData;
                    $callEventData = $this->callCrudEvent('afterSave', $request, $model, $arrInput);
                    if($callEventData && $callEventData !== true) return $callEventData;
                    // du lieu tra ve sau cung
                    
                    $callEventData = $this->fire($act . 'd', $this, $request, $model, $arrInput);
                    if($callEventData && $callEventData !== true) return $callEventData;
                    $callEventData = $this->fire('saved', $this, $request, $model, $arrInput);
                    if($callEventData && $callEventData !== true) return $callEventData;
                    $data = $this->repository->mode('mask')->detail($model->{$this->repository->getKeyName()});
                    if ($rss = $this->callCrudEvent('saveSuccess', $request, $data, $arrInput)) {
                        return $rss;
                    }
                    if ($rs = $this->fire('saveSuccess', $this, $request, $data, $arrInput)) {
                        foreach ($rs as $r) {
                            if ($r) return $r;
                        }
                    }
                    

                    $status = true;
                } else {
                    $message = 'Lỗi không xác định!';
                }
            }
        }

        $response = $this->json(compact(...$this->apiSystemVars));

        return $response;
    }

    /**
     * set data khi redirect
     *
     * @param array|string $key
     * @param mixed $value
     * @return void
     */
    public function addRedirectData($key, $value = null)
    {
        if (is_array($key)) $this->redirectData = array_merge($this->redirectData, $key);
        if (is_string($key)) $this->redirectData[$key] = $value;
    }
}
