<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\News;
use App\Models\NewsCategory;

class NewsController extends Controller
{

    public function get(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $news = News::find($id);
        return $this->success($news);
    }

    //帮助中心,新闻分类
    public function getCategory()
    {
        $results = NewsCategory::where('is_show', 1)->orderBy('sorts')->get(['id', 'name'])->toArray();
        return $this->success($results);
    }

    //推荐新闻
    public function recommend()
    {
        $results = News::where('recommend', 1)->orderBy('id', 'desc')->get(['id', 'title', 'c_id'])->toArray();
        return $this->success($results);
    }

    //获取最新的公告信息
    public function get_last_notice(Request $request)
    {
        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'zh';

        $results = News::where(['c_id'=>4,'lang'=>$lang])->orderBy('create_time', 'desc')->get(['id', 'title','content', 'c_id','create_time'])->first();
        return $this->success('操作成功',$results);
    }

    public function list(Request $request)
    {
        $limit = $request->get('limit', 15);
        $page = $request->get('page', 1);
        $category_id = $request->get('classfiy');
        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'zh';
 
        $query=News::where('lang', $lang);
        if (!empty($category_id)) {
            $query = $query
            ->where('c_id', $category_id);     
              
        }  
        $query= $query ->orderBy('sorts', 'desc')
        ->orderBy('id', 'desc')
        ->paginate($limit, ['*'], 'page', $page);

        //dd($article);
        $news=[];
        foreach ($query->items() as $item) {
          $news[]=[
            'id' => $item['id'],
            'classify' =>$item['c_id'],
            'title' => $item['title'],
            'origin' => $item['origin'],
            'pic' => NULL,
            'content' =>$item['summary'],
            'istop' => NULL,
            'sort' => NULL,
            'ctime' => $item['create_time'],
            'mtime' => NULL      
          ];
        }
              
        $result=[
            'code'=>200,
            'msg'=>0,
            'pages'=> $query->lastPage(),
            'rows'=>$news,
            'total'=>$query->total()
        ];
        return $this->success_custom($result);
    }

    // 获取分类下的文章
    public function getArticle(Request $request)
    {
        $limit = $request->get('limit', 15);
        $page = $request->get('page', 1);
        $category_id = $request->get('classfiy');
        $lang = $request->get('lang', '') ?: session()->get('lang');
        $lang == '' && $lang = 'zh';
        if (empty($category_id)) {
            $article = News::where('lang', $lang)
                ->orderBy('sorts', 'desc')
                ->orderBy('id', 'desc')
                ->paginate($limit);
        } else {
            $article = News::where('lang', $lang)
                ->where('c_id', $category_id)
                ->orderBy('sorts', 'desc')
                ->orderBy('id', 'desc')
                ->paginate($limit, ['*'], 'page', $page);
        }
        //dd($article);
        $casrousels=[];
        foreach ($article->items() as &$value) {
            $casrousels[]=[
                "content"=>'',
                "ctime"=>0,
                "enabled"=>1,
                "id"=>$value['id'],
                "link"=>'',
                "mtime"=>null,
                "positionId"=>0,
                "sort"=>$value['sorts'],
                "title"=>$value['title'],
                "url"=>$value['thumbnail'],
            ];
        }
        
      

        // return $this->success(array(
        //     "list" => $article->items(), 'count' => $article->total(),
        //     "page" => $page, "limit" => $limit
        // ));
        return $this->success('操作成功',['ads'=>[],"casrousels"=>$casrousels]);
    }

    //获取返佣规则新闻
    public function getInviteReturn()
    {

        $c_id = 23;//返佣类型
        $news = News::where('c_id', $c_id)->orderBy('id', 'desc')->first();
        if (empty($news)) {
            return $this->error('新闻不存在');
        }
        $data['news'] = $news;
        //相关新闻
        $article = News::where('c_id', $c_id)->where('id', '<>', $news->id)->orderBy('id', 'desc')->get(['id', 'c_id', 'title'])->toArray();

        $data['relation_news'] = $article;
        return $this->success($data);
    }
}