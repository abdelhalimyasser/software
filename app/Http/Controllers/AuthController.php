<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\AuthService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
// use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;


/**
 * Class AuthController
 *
 * @version 1.0
 * @since 29-04-2026
 * @author Abdelhalim Yasser
 */
class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * This is register function that handles the registration of both candidates and employees.
     * It validates the incoming request data, processes file uploads for
     * profile pictures, resumes, and documents, and then delegates the actual registration logic to the AuthService.
     * The function also includes error handling to manage potential issues during file uploads and registration,
     * returning appropriate JSON responses based on the outcome.
     *
     * <ul>
     *     <li>
     *         It uses the RegisterUserRequest to validate the incoming data, ensuring that all required fields are present and meet the specified criteria.
     *     </li>
     *      <li>
     *          It uses the AuthService to handle the registration logic, which abstracts away the details of creating user records and generating authentication tokens.
     *      </li>
     * </ul>
     *
     * @param RegisterUserRequest $request
     * @return JsonResponse
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            if (isset($data['profile_picture'])) {
                try {
                    $data['profile_picture_path'] = $request->file('profile_picture')->store('profile_pictures', 'public');
                    unset($data['profile_picture']);
                } catch (Exception $e) {
                    return response()->json([
                        'error' => 'Failed to upload profile picture: ' . $e->getMessage()
                    ], 500);
                }
            }

            if (isset($data['resume'])) {
                try {
                    $data['resume_path'] = $request->file('resume')->store('resumes', 'public');
                    unset($data['resume_path']);
                } catch (Exception $e) {
                    return response()->json([
                        'error' => 'Failed to upload resume: ' . $e->getMessage()
                    ], 500);
                }
            }

            if (isset($data['docs'])) {
                try {
                    $data['docs_path'] = $request->file('docs')->store('docs', 'public');
                    unset($data['docs_path']);
                } catch (Exception $e) {
                    return response()->json([
                        'error' => 'Failed to upload documents: ' . $e->getMessage()
                    ], 500);
                }
            }

            ($data['role'] === 'candidate')
                ? [$user, $token] = $this->authService->registerCandidate($data)
                : [$user, $token] = $this->authService->registerEmployee($data);

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ],500);
        }
    }

    /**
     * This is the login function that handles user authentication.
     * It validates the incoming request data to ensure that the email and password are provided and meet the specified criteria.
     * The function then uses the AuthService to attempt to authenticate the user with the provided credentials.
     * If authentication is successful, it returns a JSON response containing the authenticated user's information and an authentication token.
     * If authentication fails, it catches the exception and returns a JSON response with an error message and a 401 status code, indicating unauthorized access.
     *
     * <ul>
     *      <li>
     *          It uses the AuthService to handle the login logic, which abstracts away the details of verifying user credentials and generating authentication tokens.
     *      </li>
     * <ul></ul>
     *
     * @param LoginUserRequest $request
     * @return JsonResponse
     */
    public function login(LoginUserRequest $request): JsonResponse
    {
        try {
            $request->validated();
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to validate request: ' . $e->getMessage()
            ], 400);
        }

        try {
            $credentials = $request->only('email', 'password');

            [$user, $token] = $this->authService->login($credentials['email'], $credentials['password']);

            return response()->json([
                'user' => $user,
                'token' => $token
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * This is the logout function that handles user logout by deleting the current access token associated with the authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $validatedData = $request->validated();
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to validate request: ' . $e->getMessage()
            ], 400);
        }

        if ($request->hasFile('profile_picture')) {
            try {
                $validatedData['profile_picture_path'] = $request->file('profile_picture')->store('profile_pictures', 'public');
                unset($validatedData['profile_picture']);
            } catch (Exception $e) {
                return response()->json([
                    'error' => 'Failed to upload profile picture: ' . $e->getMessage()
                ], 500);
            }
        }

        if($request->hasFile('resume')) {
            try {
                $validatedData['resume_path'] = $request->file('resume')->store('resumes', 'public');
                unset($validatedData['resume']);
            } catch (Exception $e) {
                return response()->json([
                    'error' => 'Failed to upload resume: ' . $e->getMessage()
                ], 500);
            }
        }

        if ($request->hasFile('docs')) {
            try {
                $validatedData['docs_path'] = $request->file('docs')->store('docs', 'public');
                unset($validatedData['docs']);
            } catch (Exception $e) {
                return response()->json([
                    'error' => 'Failed to upload documents: ' . $e->getMessage()
                ], 500);
            }
        }

        try {
            $user->update($validatedData);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user->fresh()
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    public function forgetPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent to your email.'])
            : response()->json(['error' => 'Unable to send reset link.'], 500);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => [
                'required', 'string', 'confirmed',
                \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()->symbols()
            ],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(\Illuminate\Support\Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password has been successfully reset.'])
            : response()->json(['error' => 'Invalid token or email.'], 400);
    }
}
