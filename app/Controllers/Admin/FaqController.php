<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Content\Clinic;
use App\Content\FaqData;
use App\Core\Response;
use App\Core\Session;
use App\Models\Faq;
use App\Models\User;
use Throwable;

/**
 * FAQ CRUD. Available as a recovery path when the database is unavailable:
 * the public FAQ page falls back to the bundled content in app/Content.
 */
final class FaqController extends AdminController
{
    public function index(): Response
    {
        $this->guard();

        return $this->view('admin/faqs', [
            'page'       => 'faqs',
            'clinic'     => Clinic::info(),
            'rows'       => Faq::all(),
            'categories' => Faq::categories(),
        ]);
    }

    public function create(): Response
    {
        $this->guard();

        return $this->view('admin/faq-form', [
            'page'       => 'faqs',
            'clinic'     => Clinic::info(),
            'faq'        => null,
            'categories' => Faq::categories(),
        ]);
    }

    public function store(): Response
    {
        $this->assertCsrf();
        $this->guard();

        $data = $this->validateForm();
        if ($data === null) {
            $this->validationFailed($this->lastValidator, '/admin/faqs/new');
        }

        /** @var array<string,mixed> $data */
        try {
            $id = Faq::create($data);
            Session::flashSuccess('FAQ entry saved.');

            return $this->redirect('/admin/faqs?highlight=' . $id);
        } catch (Throwable $e) {
            Session::flashValidation(
                ['form' => 'The FAQ could not be saved: the database is not available. ' . $e->getMessage()],
                $this->safeOld($this->request->all())
            );

            return $this->redirect('/admin/faqs/new');
        }
    }

    public function edit(string $id = ''): Response
    {
        $this->guard();

        $faq = Faq::find((int) $id);
        if ($faq === null) {
            Session::flashError('That FAQ entry could not be found.');

            return $this->redirect('/admin/faqs');
        }

        return $this->view('admin/faq-form', [
            'page'       => 'faqs',
            'clinic'     => Clinic::info(),
            'faq'        => $faq,
            'categories' => Faq::categories(),
        ]);
    }

    public function update(string $id = ''): Response
    {
        $this->assertCsrf();
        $this->guard();

        $faqId = (int) $id;
        if ($faqId < 1 || Faq::find($faqId) === null) {
            Session::flashError('That FAQ entry could not be found.');

            return $this->redirect('/admin/faqs');
        }

        $data = $this->validateForm();
        if ($data === null) {
            $this->validationFailed($this->lastValidator, '/admin/faqs/' . $faqId . '/edit');
        }

        /** @var array<string,mixed> $data */
        try {
            Faq::update($faqId, $data);
            Session::flashSuccess('FAQ entry updated.');

            return $this->redirect('/admin/faqs?highlight=' . $faqId);
        } catch (Throwable $e) {
            Session::flashValidation(
                ['form' => 'The FAQ could not be updated: ' . $e->getMessage()],
                $this->safeOld($this->request->all())
            );

            return $this->redirect('/admin/faqs/' . $faqId . '/edit');
        }
    }

    public function delete(string $id = ''): Response
    {
        $this->assertCsrf();
        $this->guard();

        $faqId = (int) $id;
        if ($faqId < 1) {
            Session::flashError('Invalid FAQ reference.');

            return $this->redirect('/admin/faqs');
        }

        $confirm = $this->request->string('confirm');
        if ($confirm !== (string) $faqId && $confirm !== 'yes') {
            Session::flashError('Deletion cancelled — confirmation did not match.');

            return $this->redirect('/admin/faqs');
        }

        try {
            $deleted = Faq::delete($faqId) > 0;
            Session::flash($deleted ? 'success' : 'error', $deleted
                ? 'FAQ entry deleted.'
                : 'That FAQ entry could not be deleted.');
        } catch (Throwable $e) {
            Session::flashError('The FAQ could not be deleted: ' . $e->getMessage());
        }

        return $this->redirect('/admin/faqs');
    }

    /** @return array<string,mixed>|null */
    private function validateForm(): ?array
    {
        $this->lastValidator = $this->validate([
            'question'    => 'required|string|min:8|max:255',
            'answer'      => 'required|string|min:20|max:5000',
            'category'    => 'required|string|max:80',
            'sort_order'  => 'required|integer|gte:0|lte:9999',
            'is_published'=> 'required|integer|gte:0|lte:1',
        ]);

        $category = $this->lastValidator->string('category');
        if ($category !== '' && !in_array($category, FaqData::categories(), true) && !in_array($category, Faq::categories(), true)) {
            $this->lastValidator->reject(
                'category',
                'Use one of the existing categories, or add a new category to app/Content/FaqData.php first.'
            );
        }

        if ($this->lastValidator->fails()) {
            return null;
        }

        return [
            'question'     => $this->lastValidator->string('question'),
            'answer'       => $this->lastValidator->string('answer'),
            'category'     => $category,
            'sort_order'   => (int) $this->lastValidator->value('sort_order', 0),
            'is_published' => (int) $this->lastValidator->value('is_published', 1),
        ];
    }

    private function guard(): void
    {
        if (User::check()) {
            return;
        }

        throw new \App\Core\Exceptions\HttpException(
            302,
            'Redirecting to sign in',
            ['Location' => $this->request->url('/admin/login')]
        );
    }
}
