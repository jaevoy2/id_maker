<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Exception;
use App\Models\Student;
use App\Models\Section;
use App\Models\Strand;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentImport;
use App\Models\Subject;

class StudentController extends Controller
{
    public function list() {
        return response()->json([
            'students' => Student::with(['section', 'strand', 'subjects'])->paginate(10)
        ]);
    }

    public function subjectRoster(Request $request) {
        $students = Student::with(['section', 'strand', 'subjects'])
        ->whereHas('subjects', function ($query) use ($request) {
            $query->where('subjects.id', $request->id);
        })->paginate(10);

        return response()->json([
            'students' => $students
        ]);
    }

    public function unpaginatedList() {
                return response()->json([
            'students' => Student::with('subjects')->get()
        ]);
    }

    public function create(Request $request) {
        try {
            $validate = $request->validate([
                'firstname' => ['required', 'string', 'regex:/^[A-Za-z\s]+$/'],
                'middlename' => ['nullable', 'string', 'regex:/^[A-Za-z\s]+$/'],
                'lastname' => ['required', 'string', 'regex:/^[A-Za-z\s]+$/'],
                'suffix' => 'nullable',
                'contact' => 'required',
                'emergency_contact' => 'required',
                'birthdate' => 'required',
                'age' => 'required|numeric',
                'lrn' => 'required',
                'barangay' => 'required',
                'municipality' => 'required',
                'signature' => 'required|mimes:png,jpg,jpeg',
                'section_id' => 'required',
                'year_level' => 'required|numeric',
            ]);

            $strand = Strand::find($request->strand);

            if(!$strand) {
                return response()->json([
                    'error' => 'Strand is required.'
                ]);
            }

            $tracks = [
                'Industrial Arts (IA)',
                'Family and Consumer Science (FCS)'
            ];

            if(in_array($strand->cluster, $tracks)) {
                if(empty($request->specialization)) {
                    return response()->json([
                        'error' => 'Specialization is required.'
                    ]);
                }else {
                    $validate['strand_id'] = $request->specialization;
                }
            }else {
                $validate['strand_id'] = $strand->id;
            }

            if($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->store('images', 'public');
                $validate['image'] = env('APP_URL') . $path;
            }

            if($request->hasFile('signature')) {
                $signFile = $request->file('signature');
                $signPath = $signFile->store('images', 'public');
                $validate['signature'] = env('APP_URL') . $signPath;
            }

            $student = Student::create($validate);

            $qrData = env('FRONTEND_URL') . $student->id;

            $qrcode = QrCode::create($qrData);
            $writer = new PngWriter();
            $result = $writer->write($qrcode);
            $fileName = 'qr_code/' . uniqid() . '.png';
            Storage::disk('public')->put($fileName, $result->getString());
            $qr_path = 'http://hnvs_backend.test/' . $fileName;
            $student->qr_code = $qr_path;

            $student->save();
            return response()->json([
                'message' => 'Student added successfully.',
                'path' => Storage::url($fileName)
            ], 200);

        }catch(ValidationException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function findStudent(Request $request) {
        return response()->json([
            'student' => Student::find($request->id)->load(['section', 'strand'])
        ]);
    }

    public function edit(Request $request) {
        try {
            $student = Student::find($request->id);
            $validate = $request->validate([
                'firstname' => ['nullable', 'string', 'regex:/^[A-Za-z\s]+$/'],
                'middlename' => ['nullable', 'string', 'regex:/^[A-Za-z\s]+$/'],
                'lastname' => ['nullable', 'string', 'regex:/^[A-Za-z\s]+$/'],
                'suffix' => 'nullable',
                'contact' => 'nullable',
                'emergency_contact' => 'nullable',
                'birthdate' => 'nullable',
                'age' => 'nullable|numeric',
                'lrn' => 'required',
                'barangay' => 'nullable',
                'municipality' => 'nullable',
                'signature' => 'nullable|mimes:png,jpg,jpeg',
                'image' => 'nullable|mimes:png,jpg,jpeg',
                'section_id' => 'nullable',
                'year_level' => 'nullable|numeric',
            ]);

            $strand = Strand::find($request->strand);
            $tracks = [
                'Industrial Arts (IA)',
                'Family and Consumer Science (FCS)'
            ];

            if(in_array($strand->cluster, $tracks)) {
                $validate['strand_id'] = $request->specialization;
            }else {
                $validate['strand_id'] = $strand->id;
            }

            if($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->store('images', 'public');
                $validate['image'] = $path;
            }

            if($request->hasFile('image')) {
                if($student->image && Storage::disk('public')->exists($student->image)) {
                    Storage::disk('public')->delete($student->image);
                }

                $file = $request->file('image');
                $path = $file->store('images', 'public');
                $validate['image'] = $path;
            }

            if($request->hasFile('signature')) {
                if($student->signature && Storage::disk('public')->exists($student->signature)) {
                    Storage::disk('public')->delete($student->signature);
                }

                $signFile = $request->file('signature');
                $signPath = $signFile->store('images', 'public');
                $validate['signature'] = $signPath;
            }

            $student->update($validate);
            return response()->json([
                'message' => 'Student edited successfully.'
            ], 200);

        }catch(ValidationException $e) {
            return response()->json([
                'error' => $e->errors()
            ], 422);
        }
    }

    public function search(Request $request) {
        try {
            $students = Student::query()->with(['section', 'strand', 'subjects']);

            if($request->filled('search')) {
                $search = $request->input('search');
                $students->where(function ($query) use ($search) {
                    $query->where('firstname', 'LIKE', "%$search%")
                            ->orWhere('lastname', 'LIKE', "%$search%")
                            ->orWhere('lrn', 'LIKE', "%$search%");
                });
            }

            if($request->filled('section')) {
                $section = $request->input('section');
                $students->whereHas('section', function ($query) use ($section) {
                    $query->where('name', $section);
                });
            }

            if($request->filled('strand')) {
                $strand = $request->input('strand');
                $students->whereHas('strand', function ($query) use ($strand) {
                    $query->where('cluster', $strand);
                });
            }

            return response()->json([
                'students' => $students->paginate(10)
            ]);
        }catch(Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request) {
        try {
            $student = Student::find($request->id);
            if($student->image && Storage::disk('public')->exists($student->image)) {
                Storage::disk('public')->delete($student->image);
            }

            if($student->signature && Storage::disk('public')->exists($student->signature)) {
                Storage::disk('public')->delete($student->signature);
            }

            if($student->qr_code && Storage::disk('public')->exists($student->qr_code)) {
                Storage::disk('public')->delete($student->qr_code);
            }

            $student->delete();

            return response()->json([
                'message' => 'Student deleted successfully'
            ]);

        }catch(Exception $e) {
            return response()->json([
                'error' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }

    public function import(Request $request) {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,csv'
            ]);
            $import = new StudentImport;
            Excel::import($import, $request->file('file'));

            foreach($import->rows as $row) {
                $section = Section::where('name', $row['section'])->first();
                $strand= '';

                if($row['specialization'] != '') {
                    $strand = Strand::where('specialization', $row['specialization'])->first();
                }else {
                    $strand = Strand::where('cluster', $row['strand'])->first();
                }

                if(!$section || !$strand) {
                    $skipped[] =  $row['firstname']. ' ' . $row['lastname'];
                    continue;
                }

                $student = Student::create([
                    'firstname' => $row['firstname'],
                    'middlename' => $row['middlename'],
                    'lastname' => $row['lastname'],
                    'suffix' => $row['suffix'] ?? null,
                    'barangay' => $row['barangay'],
                    'municipality' => $row['municipality'],
                    'age' => $row['age'],
                    'contact' => $row['contact'],
                    'lrn' => $row['lrn'],
                    'emergency_contact' => $row['emergency_contact'],
                    'birthdate' => $row['birthdate'],
                    'year_level' => $row['year_level'],
                    'section_id' => $section->id,
                    'strand_id' => $strand->id
                ]);

                $qrData = env('FRONTEND_URL'). '/students?id=' . $student->id;;

                $qrcode = QrCode::create($qrData);
                $writer = new PngWriter();
                $result = $writer->write($qrcode);
                $fileName = 'qr_code/' . uniqid() . '.png';
                Storage::disk('public')->put($fileName, $result->getString());
                $path = env('APP_URL') . $fileName;
                $student->qr_code = $path;
                $student->save();
            }

            return response()->json([
                'message' => 'Students imported successfully',
                'skipped' => isset($skipped) && count($skipped) > 0
                             ? 'Section or Strand is not found for student/s: ' . implode(', ', $skipped)
                             : ''
            ]);

        }catch(Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function count() {
        return response()->json([
            'students' => Student::count(),
        ]);
    }

    public function sectionStrandList() {
        return response()->json([
            'sections' => Section::all(),
            'strands' => Strand::all()
        ]);
    }

    public function subjectStudents(Request $request) {
        try {
            $student_ids = $request->ids;
            $subject_id = Subject::find($request->subject);

            $subject_id->students()->sync($student_ids);
            return response()->json([
                'message' => 'Subject roster updated successfully.'
            ]);

        }catch(Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

}
