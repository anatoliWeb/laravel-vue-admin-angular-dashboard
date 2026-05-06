<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreUserRequest;
use App\Http\Requests\Api\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Users API Controller.
 *
 * WHY:
 * - Handles HTTP layer only
 * - Delegates business logic to UserService
 * - Keeps responses consistent for frontend (SaaS API style)
 */
class UserController extends Controller
{
    /**
     * Inject UserService (business logic layer)
     */
    public function __construct(
        protected UserService $userService
    ) {
        /**
         * IMPORTANT:
         * In modern Laravel structure we prefer to define
         * permissions in routes/api.php instead of controller.
         *
         * This avoids coupling and makes routes more explicit.
         */
    }

    /**
     * Get list of users.
     *
     * WHY:
     * Used by DataTable on frontend.
     */
    public function index(): JsonResponse
    {
        $users = $this->userService->getUsersForApi();

        return response()->json([
            'data' => $users, // service already should return proper structure
        ]);
    }

    /**
     * Get single user by ID.
     *
     * WHY:
     * Used for "Edit user" modal (prefill form)
     */
    public function show(int $user): JsonResponse
    {
        $record = $this->userService->getById($user);

        return response()->json([
            'data' => $record,
        ]);
    }

    /**
     * Create new user.
     *
     * WHY:
     * Called from "Create User" modal.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $record = $this->userService->create($request->validated());

        return response()->json([
            'data' => $record,
        ], 201); // 201 = created
    }

    /**
     * Update existing user.
     *
     * WHY:
     * Called from "Edit User" modal.
     */
    public function update(UpdateUserRequest $request, int $user): JsonResponse
    {
        $record = $this->userService->update($user, $request->validated());

        return response()->json([
            'data' => $record,
        ]);
    }

    /**
     * Delete user.
     *
     * WHY:
     * Called from table "Delete" action.
     */
    public function destroy(int $user): JsonResponse
    {
        $this->userService->delete($user);

        return response()->json([
            'data' => [
                'deleted' => true,
            ],
        ]);
    }
}
