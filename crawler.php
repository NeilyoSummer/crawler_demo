<?php
require_once 'vendor/autoload.php';

/**
 * 使用依赖
 * symfony/dom-crawler
 * symfony/css-selector
 */
use Symfony\Component\DomCrawler\Crawler;

/**
 * 抓取的函数
 * @param $url
 */
function fetch($url)
{
    $response = file_get_contents($url);
    $crawler = new Crawler($response);

    if (!file_exists('./imgs')) {
        mkdir('imgs');
    }

    $crawler->filter(".bookmark-item-img > a")->each(function ($item, $index) {
        $pattern = '/http:\/\/[\w+\.\/]+/i';
        preg_match_all($pattern, $item->attr('style'), $match);
        $filename = './imgs/' . pathinfo($match[0][0])['basename'];
        file_put_contents($filename, file_get_contents($match[0][0]));
        echo $filename . ' 下载成功!' . PHP_EOL;
    });
}

// 遍历要抓取的页面数据
for ($i = 1; $i < 400; $i++) {
    $pid = pcntl_fork();    // 开启进程
    if ($pid) {
        // 只有主进程才会执行这里的代码，$count主要是控制进程数
        static $count = 0;
        $count++;
        if ($count >= 5) {
            pcntl_wait($status); //阻塞父进程，直到子进程结束
            $count--;
        }
    } else {
        // 子进程进行抓取，执行完成后 exit 告诉主进程任务结束
        $url = "http://bm.straightline.jp/?page={$i}&ajax=1";
        fetch($url);
        exit;
    }
}
