<?php

namespace Pveltrop\DCMS\Http\Controllers;

use App\User;
use Pveltrop\DCMS\Classes\Form;
use Pveltrop\DCMS\Forms\UserForm;
use App\Http\Controllers\Controller;
use Pveltrop\DCMS\Classes\Datatable;
use Illuminate\Auth\Events\Registered;
use Pveltrop\DCMS\Traits\DCMSController;
use Pveltrop\DCMS\Http\Requests\UserRequest;

class UserController extends Controller
{
    use DCMSController;

    public function __construct()
    {
        $this->routePrefix = 'user';
        $this->model = User::class;
        $this->request = UserRequest::class;
        $this->form = UserForm::class;
        $this->responses = [
            "created" => [
                "title" => __("User created"),
                "message" => __("User created on __created_at__"),
                "url" => route('dcms.portal.user.index')
            ],
            "updated" => [
                "title" => __("User updated"),
                "message" => __("User updated on __created_at__"),
                "url" => route('dcms.portal.user.index')
            ],
            "confirmDelete" => [
                "title" => __("Delete user?"),
                "message" => __("Are you sure you want to delete this user?"),
            ],
            "failedDelete" => [
                "title" => __("Failed to delete"),
                "message" => __("Unable to delete this user."),
            ],
            "deleted" => [
                "title" => __("User deleted"),
                "message" => __("User has been deleted."),
            ],
        ];
        $this->views = [
            "index" => "index",
            "show" => "show",
            "edit" => "crud",
            "create" => "crud"
        ];
    }

    // If you plan to use server side filtering/sorting/paging in the DCMS KTDatatables wrapper, define the base query below
    public function fetch(): \Illuminate\Http\JsonResponse
    {
        // Get class to make a query for
        $query = User::with('roles');
        return (new Datatable($query))->render();
    }

    public function index()
    {
        return view('dcms::user.index');
    }

    public function afterCreate($request, $model): void
    {
        if (initSMTP()) {
            try {
                event(new Registered($model));
            } catch (\Throwable $th) {
                logger("Couldn't send e-mail to user."."\n".$th->getMessage().$th->getTraceAsString());
            }
        }
    }

    public function create()
    {
        $this->initDCMS();
        // Auto generated Form with HTMLTag package
        $form = (isset($this->form)) ? Form::create($this->request, $this->routePrefix, $this->form, $this->responses) : null;
        return view('dcms::user.crud')->with(['form' => $form]);
    }

    public function edit(User $user)
    {
        $this->initDCMS();
        // Auto generated Form with HTMLTag package
        $form = (isset($this->form)) ? Form::create($this->request, $this->routePrefix, $this->form, $this->responses) : null;
        return view('dcms::user.crud')->with(['form' => $form]);
    }

    /**
     *
     * DCMS: Execute code after a new model has been created/updated/deleted
     *
     */

    public function afterCreateOrUpdate($request, User $user): void
    {
        if (isset($request['roles'])) {
            $user->syncRoles($request['roles']);
        }
    }
}
