<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\RegisterEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\AuthService;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRules;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    /**
     * Public Routes for Candidates
     */
    public function registerCandidate(RegisterUserRequest $request): JsonResponse
    {
        // try to validate data
        try {
            $data = $request->validated();
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Unprocessable Entity: ' . $e->getMessage()
            ], 422);
        }

        // try to upload profile picture
        if ($request->hasFile('profile_picture')) {
            try {
                $data['profile_picture_path'] = $request->file('profile_picture')->store('profiles', 'public');
                unset($data['profile_picture']);
            } catch (Exception $e) {
                return response()->json([
                    'error' => 'Failed to upload profile picture: ' . $e->getMessage()
                ], 500);
            }
        }

        // try to upload resume
        if ($request->hasFile('resume')) {
            try {
                $data['resume_path'] = $request->file('resume')->store('resumes', 'local');
                unset($data['resume']);
            } catch (Exception $e) {
                return response()->json([
                    'error' => 'Failed to upload resume: ' . $e->getMessage()
                ], 500);
            }
        }

        // try to upload docs
        if ($request->hasFile('docs')) {
            try {
                $data['docs_path'] = $request->file('docs')->store('documents', 'local');
                unset($data['docs']);
            } catch (Exception $e) {
                return response()->json([
                    'error' => 'Failed to upload documents: ' . $e->getMessage()
                ], 500);
            }
        }

        // try register the user
        try {
            [$user, $token] = $this->authService->registerCandidate($data);

            return response()->json([
                'message' => 'Candidate registered successfully',
                'user' => $user,
                'token' => $token
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to register candidate: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Unprocessable Entity: ' . $e->getMessage()
            ], 422);
        }

        try {
            $credentials = $request->only('email', 'password', 'emp_id');

            [$user, $token] = $this->authService->login(
                $credentials['email'],
                $credentials['password'] ?? null,
                $credentials['emp_id'] ?? null
            );

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
     * Private Routes
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
        try {
            $data = $request->validated();
        } catch (Exception $e) {
            return response()->json(['error' => 'Unprocessable Entity: ' . $e->getMessage()], 422);
        }

        // handle optional uploads
        if ($request->hasFile('profile_picture')) {
            try {
                $data['profile_picture_path'] = $request->file('profile_picture')->store('profiles', 'public');
                unset($data['profile_picture']);
            } catch (Exception $e) {
                return response()->json(['error' => 'Failed to upload profile picture: ' . $e->getMessage()], 500);
            }
        }

        if ($request->hasFile('resume')) {
            try {
                $data['resume_path'] = $request->file('resume')->store('resumes', 'local');
                unset($data['resume']);
            } catch (Exception $e) {
                return response()->json(['error' => 'Failed to upload resume: ' . $e->getMessage()], 500);
            }
        }

        if ($request->hasFile('docs')) {
            try {
                $data['docs_path'] = $request->file('docs')->store('documents', 'local');
                unset($data['docs']);
            } catch (Exception $e) {
                return response()->json(['error' => 'Failed to upload documents: ' . $e->getMessage()], 500);
            }
        }

        try {
            $user = $request->user();
            $user->update($data);

            return response()->json([
                'message' => 'Profile updated successfully',
                'user' => $user->fresh(),
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update profile: ' . $e->getMessage()], 500);
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
                PasswordRules::min(8)->mixedCase()->numbers()->symbols()
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


    /**
     * Private Routes for Employees
     */
    public function registerEmployee(RegisterEmployeeRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
        } catch (Exception $e) {
            return response()->json(['error' => 'Unprocessable Entity: ' . $e->getMessage()], 422);
        }

        // handle optional file uploads
        if ($request->hasFile('profile_picture')) {
            try {
                $data['profile_picture_path'] = $request->file('profile_picture')->store('profiles', 'public');
                unset($data['profile_picture']);
            } catch (Exception $e) {
                return response()->json(['error' => 'Failed to upload profile picture: ' . $e->getMessage()], 500);
            }
        }

        try {
            [$user, $token] = $this->authService->registerEmployee($data);

            // Generate PDF Report
            $pdfFileName = 'employee_report_' . $user->emp_id . '_' . time() . '.pdf';
            $pdfPath = 'reports/' . $pdfFileName;
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.employee_report', ['employee' => $user->fresh()]);
            \Illuminate\Support\Facades\Storage::disk('public')->put($pdfPath, $pdf->output());

            $pdfUrl = url('storage/' . $pdfPath);

            // Send Email with PDF Attachment
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\EmployeeCredentialsMail($user->fresh(), $pdfPath));

            return response()->json([
                'message' => 'Employee registered successfully',
                'employee' => $user->fresh(),
                'id' => $user->getKey(),
                'token' => $token,
                'pdf_report_url' => $pdfUrl,
            ], 201);

        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to register employee: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing employee's data.
     */
    public function updateEmployee(UpdateEmployeeRequest $request, $id): JsonResponse
    {
        try {
            $employee = Employee::findOrFail($id);
        } catch (Exception $e) {
            return response()->json(['error' => 'Employee not found.'], 404);
        }

        try {
            $data = $request->validated();
        } catch (Exception $e) {
            return response()->json(['error' => 'Unprocessable Entity: ' . $e->getMessage()], 422);
        }

        // handle optional file uploads
        if ($request->hasFile('profile_picture')) {
            try {
                $data['profile_picture_path'] = $request->file('profile_picture')->store('profiles', 'public');
                unset($data['profile_picture']);
            } catch (Exception $e) {
                return response()->json(['error' => 'Failed to upload profile picture: ' . $e->getMessage()], 500);
            }
        }

        try {
            $employee->update($data);

            return response()->json([
                'message' => 'Employee updated successfully',
                'employee' => $employee->fresh(),
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update employee: ' . $e->getMessage()], 500);
        }
    }
}
