<?php

use Modules\Iblog\Entities\Category;
use Modules\Iblog\Entities\Post;
use Modules\Iblog\Entities\Status;
use Modules\Iblog\Entities\Tag;
use Modules\User\Entities\Sentinel\User;

if (! function_exists('get_posts')) {
    function get_posts($options = [])
    {
        $default_options = [
            'categories' => null, // categoria o categorias que desee llamar, se envia como arreglo ['categories'=>[1,2,3]]
            'users' => [], //usuario o usuarios que desea llamar, se envia como arreglo ['users'=>[1,2,3]]
            'include' => [], //id de post a para incluir en una consulta, se envia como arreglo ['id'=>[1,2,3]]
            'exclude' => [], // post, categorias o usuarios, que desee excluir de una consulta metodo de llmado category=>'', posts=>'' , users=>''
            'exclude_categories' => [], // categoria o categorias que desee Excluir, se envia como arreglo ['exclude_categories'=>[1,2,3]]
            'exclude_users' => [], //usuario o usuarios que desea Excluir, se envia como arreglo ['users'=>[1,2,3]]
            'date' => null, //['from'=>date('Y-m-d H:i:s'), 'to' => date('Y-m-d H:i:s')],
            'take' => 5, //Numero de posts a obtener,
            'skip' => 0, //Omitir Cuantos post a llamar
            'order' => ['field' => 'created_at', 'way' => 'desc'], //orden de llamado
            'status' => 2,
        ];

        $options = array_merge($default_options, $options);

        $post = app('Modules\Iblog\Repositories\PostRepository');
        $params = json_decode(json_encode(['filter' => $options, 'include' => ['user', 'categories', 'category'], 'take' => $options['take'], 'skip' => $options['skip']]));

        return $post->getItemsBy($params);
    }
}

if (! function_exists('reorderCategories')) {
    function reorderCategories($options = [])
    {
        $default_options = [
            'categories' => null, // categoria o categorias que desee llamar, se envia como arreglo ['categories'=>[1,2,3]]
            'users' => [], //usuario o usuarios que desea llamar, se envia como arreglo ['users'=>[1,2,3]]
            'include' => [], //id de post a para incluir en una consulta, se envia como arreglo ['id'=>[1,2,3]]
            'exclude' => [], // post, categorias o usuarios, que desee excluir de una consulta metodo de llmado category=>'', posts=>'' , users=>''
            'exclude_categories' => [], // categoria o categorias que desee Excluir, se envia como arreglo ['exclude_categories'=>[1,2,3]]
            'exclude_users' => [], //usuario o usuarios que desea Excluir, se envia como arreglo ['users'=>[1,2,3]]
            'date' => null, //['from'=>date('Y-m-d H:i:s'), 'to' => date('Y-m-d H:i:s')],
            'take' => 5, //Numero de posts a obtener,
            'skip' => 0, //Omitir Cuantos post a llamar
            'status' => 1,
        ];
        $order = $options['order'] ?? ['field' => 'created_at', 'way' => 'desc'];
        unset($options['order']);
        $options = array_merge($default_options, $options);

        $categoryRepository = app('Modules\Iblog\Repositories\CategoryRepository');
        $params = json_decode(json_encode(['filter' => $options, 'take' => $options['take'], 'order' => $order, 'skip' => $options['skip']]));

        $categories = $categoryRepository->getItemsBy($params);

        foreach ($categories as $key => $category) {
            $category->sort_order = $key;
            $category->save();
        }

        return [
            'ordered' => $categories->count(),
        ];
    }
}
if (! function_exists('reorderPosts')) {
    function reorderPosts($options = [])
    {
        $default_options = [
            'categories' => null, // categoria o categorias que desee llamar, se envia como arreglo ['categories'=>[1,2,3]]
            'users' => [], //usuario o usuarios que desea llamar, se envia como arreglo ['users'=>[1,2,3]]
            'include' => [], //id de post a para incluir en una consulta, se envia como arreglo ['id'=>[1,2,3]]
            'exclude' => [], // post, categorias o usuarios, que desee excluir de una consulta metodo de llmado category=>'', posts=>'' , users=>''
            'exclude_categories' => [], // categoria o categorias que desee Excluir, se envia como arreglo ['exclude_categories'=>[1,2,3]]
            'exclude_users' => [], //usuario o usuarios que desea Excluir, se envia como arreglo ['users'=>[1,2,3]]
            'date' => null, //['from'=>date('Y-m-d H:i:s'), 'to' => date('Y-m-d H:i:s')],
            'take' => 5, //Numero de posts a obtener,
            'skip' => 0, //Omitir Cuantos post a llamar
            'status' => 1,
        ];
        $order = $options['order'] ?? ['field' => 'created_at', 'way' => 'desc'];
        unset($options['order']);
        $options = array_merge($default_options, $options);

        $postRepository = app('Modules\Iblog\Repositories\PostRepository');
        $params = json_decode(json_encode(['filter' => $options, 'take' => $options['take'], 'order' => $order, 'skip' => $options['skip']]));

        $posts = $postRepository->getItemsBy($params);

        foreach ($posts as $key => $post) {
            $post->sort_order = $key;
            $post->save();
        }

        return [
            'ordered' => $post->count(),
        ];
    }
}

if (! function_exists('all_users_by_rol')) {
    function all_users_by_rol($roleName)
    {
        $users = User::with(['roles'])
            ->whereHas('roles', function ($query) use ($roleName) {
                $query->where('name', '=', $roleName);
            })->latest()->get();

        return $users;
    }
}

if (! function_exists('get_categories')) {
    function get_categories($options = [])
    {
        $default_options = [
            'include' => [], //id de Categorias  para incluir en una consulta, se envia como arreglo ['include'=>[1,2,3]]
            'exclude' => [], //id de categorias  que desee excluir de una consulta metodo de llmado category=>'[1,2]'
            'parent' => '0', //id de categorias  padre que desee mostrar en una consulta metodo de llmado category=>'[1,2]'
            'take' => 5, //Numero de posts a obtener,
            'skip' => 0, //Omitir Cuantos post a llamar
            'order' => 'desc', //orden de llamado
        ];

        $options = array_merge($default_options, $options);

        $categories = Category::query();
        if (! empty($options['exclude'])) {
            $categories->whereNotIn('id', $options['exclude']);
        }
        if (! empty($options['parent'])) {
            $categories->where('parent_id', $options['parent']);
        }
        $categories->skip($options['skip'])
            ->take($options['take'])
            ->orderBy('created_at', $options['order']);
        if (! empty($options['include'])) {
            $categories->orWhere(function ($query) use ($options) {
                $query->whereIn('id', $options['include']);
            });
        }

        return $categories->get();
    }
}

if (! function_exists('get_tags')) {
    function get_tags($options = [])
    {
        $default_options = [
            'include' => [], //id de Categorias  para incluir en una consulta, se envia como arreglo ['include'=>[1,2,3]]
            'exclude' => [], //id de categorias  que desee excluir de una consulta metodo de llmado category=>'[1,2]'
            'take' => 5, //Numero de posts a obtener,
            'skip' => 0, //Omitir Cuantos post a llamar
            'order' => 'desc', //orden de llamado
        ];

        $options = array_merge($default_options, $options);

        $tags = Tag::query();

        if (! empty($options['exclude'])) {
            $tags->whereNotIn('id', $options['exclude']);
        }
        if (! empty($options['parent'])) {
            $tags->where('parent_id', $options['parent']);
        }
        $tags->skip($options['skip'])
            ->take($options['take'])
            ->orderBy('created_at', $options['order']);
        if (! empty($options['include'])) {
            $tags->orWhere(function ($query) use ($options) {
                $query->whereIn('id', $options['include']);
            });
        }

        return $tags->get();
    }
}

if (! function_exists('format_date')) {
    /**
     * Format date according to local module configuration.
     *
     * @param  object  $date
     * @param  string  $format
     * @return string
     **/
    function format_date($date, $format = '%A, %B %d, %Y')
    {
        return strftime($format, strtotime($date));
    }
}

if (! function_exists('postgallery')) {
    function postgallery($id)
    {
        $images = Storage::disk('publicmedia')->files('assets/iblog/post/gallery/'.$id);

        return $images;
    }
}

date_default_timezone_set(config('asgard.iblog.config.dateTimezone', 'America/Bogota'));
setlocale(LC_TIME, config('asgard.iblog.config.localeTime', 'en_US.UTF-8'));

if (! function_exists('get_status')) {
    function get_status($status)
    {
        switch ($status) {
            case trans('iblog::common.status.draft'):
                return Status::DRAFT;
                break;
            case trans('iblog::common.status.pending'):
                return Status::PENDING;
                break;
            case trans('iblog::common.status.published'):
                return Status::PUBLISHED;
                break;
            case trans('iblog::common.status.unpublished'):
                return Status::UNPUBLISHED;
                break;
            default:
                return Status::PUBLISHED;
        }
    }
}
