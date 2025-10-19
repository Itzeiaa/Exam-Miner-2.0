<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Exam;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Auth::user()->exams()->latest()->get();
        return view('my-exams', compact('exams'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'exam_type' => 'required|string',
            'number_of_questions' => 'required|integer|min:1|max:100',
            'sets_of_exam' => 'required|integer|min:1|max:10',
            'learning_material' => 'required|file|mimes:docx,ppt,pptx,pdf|max:10240', // 10MB max
        ]);

        // Handle file upload
        $file = $request->file('learning_material');
        $filename = time() . '_' . Auth::id() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads'), $filename);

        $exam = Auth::user()->exams()->create([
            'title' => $request->title,
            'description' => $request->description,
            'exam_type' => $request->exam_type,
            'number_of_questions' => $request->number_of_questions,
            'sets_of_exam' => $request->sets_of_exam,
            'learning_material' => $filename, // Store filename in database
        ]);

        return redirect('/my-exams')->with('success', 'Exam generated successfully!');
    }

    public function show($id)
    {
        $exam = Auth::user()->exams()->findOrFail($id);
        return view('exam-view', compact('exam'));
    }

    public function download($id)
    {
        $exam = Auth::user()->exams()->findOrFail($id);
        
        // For now, we'll create a simple text file with exam details
        // In a real application, you'd generate a proper PDF or Word document
        $content = "EXAM: {$exam->title}\n";
        $content .= "Description: {$exam->description}\n";
        $content .= "Type: {$exam->exam_type}\n";
        $content .= "Questions: {$exam->number_of_questions}\n";
        $content .= "Sets: {$exam->sets_of_exam}\n";
        $content .= "Created: {$exam->created_at->format('M d, Y H:i')}\n\n";
        $content .= "Note: This is a placeholder. In a real application, this would contain the actual generated questions.\n";
        $content .= "The AI would have analyzed your uploaded material and created relevant questions based on your specifications.";

        $filename = 'exam_' . $exam->id . '_' . now()->format('Y-m-d') . '.txt';
        
        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function destroy($id)
    {
        $exam = Auth::user()->exams()->findOrFail($id);
        $exam->delete();
        
        return redirect('/my-exams')->with('success', 'Exam deleted successfully!');
    }
}