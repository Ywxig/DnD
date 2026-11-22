<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;
use App\Validators\PostValidator;

/**
 * Посты-объявления об играх: лента, свои посты, создание и просмотр.
 *
 * @package App\Controllers
 */
final class PostController extends Controller
{
    /** Лента всех постов. */
    public function index(): void
    {
        $this->requireLogin();

        $this->render('posts/index', [
            'posts' => Post::allDetailed(),
            'title' => 'Посты',
        ]);
    }

    /** Посты текущего пользователя. */
    public function my(): void
    {
        $this->requireLogin();

        $this->render('posts/index', [
            'posts' => Post::ownedBy((int)$_SESSION['user_id']),
            'title' => 'Мои посты',
            'show_my_link' => false,
        ]);
    }

    /** Форма создания (GET) и сохранение поста (POST). */
    public function create(): void
    {
        $this->requireLogin();

        if (!$this->isPost()) {
            $this->render('posts/create', [
                'data'   => [],
                'errors' => [],
                'action' => url('posts_create'),
                'title'  => 'Новый пост',
            ]);
            return;
        }

        assert_csrf();

        $data = [
            'title'       => $this->post('title'),
            'format'      => $this->post('format'),
            'start_level' => (($_POST['start_level'] ?? '') !== '') ? (int)$_POST['start_level'] : null,
            'description' => trim((string)($_POST['description'] ?? '')),
        ];

        $errors = PostValidator::validate($data);

        if ($errors !== []) {
            flash('error', 'Проверьте форму — есть ошибки.');
            $this->render('posts/create', [
                'data'   => $data,
                'errors' => $errors,
                'action' => url('posts_create'),
                'title'  => 'Новый пост',
            ]);
            return;
        }

        $data['user_id'] = (int)$_SESSION['user_id'];
        $id = Post::create($data);

        flash('success', 'Пост «' . $data['title'] . '» создан.');
        $this->redirect('post_view', ['id' => $id]);
    }

    /** Карточка поста. */
    public function view(): void
    {
        $this->requireLogin();

        $post = Post::findDetailed((int)($_GET['id'] ?? 0));
        if ($post === null) {
            http_response_code(404);
            $this->render('errors/404');
            return;
        }

        $this->render('posts/view', [
            'post'  => $post,
            'title' => $post['title'],
        ]);
    }
}
