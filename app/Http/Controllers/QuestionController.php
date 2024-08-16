<?php

namespace App\Http\Controllers;

use App\Models\McqOption;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $question = Question::query()
            ->where([
                'chapter_id' => $request->chapter_id,
                'type' => $request->question_type,
            ]);

        if($request->question_type === 'MCQ') {
            $question->with([
                'mcqOptions',
            ]);
        }

        return response()->json($question->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'type' => 'required|in:MCQ,Written',
            'question_text' => 'required|string',
        ]);

        $question = Question::create($validated);

        if($request->type == 'MCQ') {
            foreach ($request->mcq_options as $option) {
                if($option['option_text']) {
                    McqOption::create([
                        'question_id' => $question->id,
                        'option_text' => $option['option_text'],
                        'is_correct'  => $option['is_correct'],
                    ]);
                }
            }

            $question->load('mcqOptions');
        }

        return response()->json($question, 201);
    }

    public function show($id)
    {
        $question = Question::with(['mcqOptions', 'writtenAnswers'])->findOrFail($id);

        return response()->json($question);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'chapter_id' => 'sometimes|exists:chapters,id',
            'type' => 'sometimes|in:MCQ,Written',
            'question_text' => 'sometimes|string',
        ]);

        $question = Question::findOrFail($id);

        $question->update($validated);

        if($question->type == 'MCQ') {
            $option_ids = [];

            foreach ($request->mcq_options as $option) {
                if($option['option_text']) {
                    $option = McqOption::updateOrCreate(
                        [
                            'id'          => $option['id'] ?? null,
                            'question_id' => $question->id,
                        ],
                        [           
                            'option_text' => $option['option_text'] ?? '',
                            'is_correct'  => $option['is_correct'] ?? false,
                        ]
                    );

                    $option_ids[] = $option->id;
                }
            }
            
            McqOption::query()
                ->where('question_id', $question->id)
                ->whereNotIn('id', $option_ids)
                ->delete();
        
            $question->load('mcqOptions');
        }

        return response()->json($question);
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();

        return response()->json(null, 204);
    }
}
