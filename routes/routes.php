<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 1/8/23
 * Time: 9:34 AM
 */

use Illuminate\Support\Facades\Route;

//获取应用token（登录）
Route::resource('/examinations', 'ExaminationController');
Route::resource('/user/examinations', 'UserExaminationController');
Route::resource('/user/examination/{exam_id}/subjects', 'UserExaminationSubjectController');
Route::resource('/subject', 'SubjectController');
Route::resource('/question_banks', 'QuestionBankController');
Route::resource('/question_bank/{bank_id}/questions', 'QuestionController');
Route::resource('/examination/{exam_id}/subjects', 'ExaminationSubjectController');
Route::get('/api/subject_bank', 'ExaminationSubjectController@bankOptions');
Route::resource('/paper/{paper_id}/questions', 'PaperQuestionController');
Route::get('/user/examination_subject/{examination_subject_id}/start', 'UserExamStartController@index');
Route::post('/user/examination_subject/{examination_subject_id}/commit', 'UserExamStartController@commit');
Route::get('/user/examination_subject/{user_examination_subject_id}/success', 'UserExamStartController@success');
Route::resource('/user_examination_histories', 'UserExaminationSubjectRecordController');
Route::get('/user_examination_history/{id}', 'UserExaminationSubjectRecordController@form');
Route::get('/user_examination_range', 'UserExaminationRangeController@index');
Route::get('/examination/range/{examId}', 'UserExaminationRangeController@exam');
Route::get('/subject/range/{subjectId}', 'UserExaminationRangeController@subject');
