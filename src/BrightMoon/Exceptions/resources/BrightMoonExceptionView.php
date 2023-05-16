<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?=$message?></title>
        <style>
            <?php include __DIR__.'/css/style.css';?>
        </style>
    </head>
    <body>
        <div class="container">
            <header class="card">
                <div class="content">
                    <div class="info">
                        <div class="class"><?=$class?></div>
                        <div class="version">
                            <span>PHP <?=phpversion()?></span>
                            <span>BrightMoon <?=app()::VERSION?></span>
                        </div>
                    </div>
                    <div class="message"><?=$message?></div>
                </div>
            </header>
            <section class="navigation card">
                <nav>
                    <ul>
                        <li>
                            <a href="#stack">Stack</a>
                        </li>
                        <li>
                            <a href="#request">Request</a>
                        </li>
                        <li>
                            <a href="#routing">Routing</a>
                        </li>
                    </ul>
                </nav>
            </section>
            <div class="main card">
                <div id="stack" class="row">
                    <div class="col col-4">
                        <?php foreach ($traces as $trace) : ?>
                            <?php
                                $itemContent = '';

                                if (isset($trace['file'])) {
                                    $itemContent .= $trace['file'];
                                    if (isset($trace['line'])) {
                                        $itemContent .= ' : ' . $trace['line'];
                                    }
                                }

                                if (isset($trace['class'])) {
                                    $itemContent .= $itemContent ? "\n{$trace['class']}" : $trace['class'];
                                }
                            ?>
                            <div class="item" title="<?=$itemContent?>">
                                <?=$itemContent?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="col col-8">
                        <div class="code-path">
                            <a href="<?=$linkToEditor?>" title="Open for editing">
                                <?=$file?>:<?=$line?>
                            </a>
                        </div>
                        <div class="code-content">
                            <?php
                                $codeContent = '';
                                foreach ($contentFile as $val) {
                                    if ($val['line'] == $line) {
                                        $codeContent .= '<div class="line hightlight"><span class="line-number">'
                                        . $val['line'] . ':</span> <span class="line-code">'
                                        . $val['content'] . '</span></div>';
                                    } else {
                                        $className = 'line';

                                        if (preg_match('/\/\*\*|\*[\s\n]+|\/\/|\*\//', $val['content'])) {
                                            $className .= ' line-comment';
                                        }

                                        $codeContent .= '<div class="'.$className.'"><span class="line-number">'
                                        .$val['line'].':</span> <span class="line-code">'
                                        .$val['content'].'</span></div>';
                                    }
                                }
                                $codeContent = str_replace('<?php', '&lt;&quest;php', $codeContent);
                            ?>
                            <pre><code data-lang="php"><?=$codeContent?></code></pre>
                            <?php
                                if (count($contentFile) < 29) {
                                    $lastLine = end($contentFile);

                                    for ($i = 0; $i < 29 - count($contentFile); $i++) {
                                        echo '<pre><code data-lang="php">'
                                        . '<div class="line"><span class="line-number">'
                                        . $lastLine['line'] + $i + 1
                                        . ':</span> <span class="line-code"></span></div>'
                                        . '</code></pre>';
                                    }
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <section class="card">
                <div class="row">
                    <div class="col col-3 left-sidebar">
                        <div id="request" class="menu-title">Request</div>
                        <ul class="menu">
                            <li>
                                <a href="#headers">Headers</a>
                            </li>
                            <li>
                                <a href="#body">Body</a>
                            </li>
                            <?php if (! empty($request->query())) : ?>
                                <li>
                                    <a href="#query">Query</a>
                                </li>
                            <?php endif ?>
                            <?php if (! empty($request->cookie())) : ?>
                                <li>
                                    <a href="#cookies">Cookies</a>
                                </li>
                            <?php endif ?>
                        </ul>
                        <div class="menu-title">App</div>
                        <ul class="menu">
                            <li>
                                <a href="#routing">Routing</a>
                            </li>
                        </ul>
                        <div class="menu-title">Context</div>
                        <ul class="menu">
                            <li>
                                <a href="#information">Information</a>
                            </li>
                        </ul>
                    </div>

                    <div class="col col-9">
                        <div class="block-items">
                            <h1>Request</h1>
                            <div class="request-info">
                                <div class="request-url"><?=$request->url()?></div>
                                <div class="request-method"><?=$request->method()?></div>
                            </div>
                            <div class="item">
                                <h2 id="headers">Headers</h2>
                                <?php foreach ($request->headers['header'] as $key => $value) : ?>
                                    <div class="group-item">
                                        <div class="item-key col-3"><?=$key?></div>
                                        <div class="item-value col-9">
                                            <pre><code><?=$value?></code></pre>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                            <div class="item">
                                <h2 id="body">Body</h2>
                                <div class="group-item">
                                    <div class="item-value w-100">
                                        <pre><code><?=json_encode($request->request, JSON_PRETTY_PRINT)?></code></pre>
                                    </div>
                                </div>
                            </div>
                            <?php if (! empty($request->query())) : ?>
                                <div class="item">
                                    <h2 id="query">Query</h2>
                                    <?php foreach ($request->query() as $key => $value) : ?>
                                        <div class="group-item">
                                            <div class="item-key col-3"><?=$key?></div>
                                            <div class="item-value col-9">
                                                <pre><code><?=is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value?></code></pre>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            <?php endif ?>
                            <?php if (! empty($request->cookie())) : ?>
                                <div class="item">
                                    <h2 id="cookies">Cookies</h2>
                                    <?php foreach ($request->cookie() as $key => $value) : ?>
                                        <div class="group-item">
                                            <div class="item-key col-3"><?=$key?></div>
                                            <div class="item-value col-9">
                                                <pre><code><?=is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : $value?></code></pre>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            <?php endif ?>
                        </div>

                        <div class="block-items">
                            <h1>App</h1>
                            <div class="item">
                                <h2 id="routing">Routing</h2>

                                <div class="group-item">
                                    <div class="item-key col-3">Name</div>
                                    <div class="item-value col-9"><?=$currentRoute->getName()?></div>
                                </div>

                                <div class="group-item">
                                    <div class="item-key col-3">Controller</div>
                                    <div class="item-value col-9"><?=$currentRoute->getController()?></div>
                                </div>
                            </div>
                        </div>

                        <div class="block-items">
                            <h1>Context</h1>
                            <div class="item">
                                <h2 id="information">Information</h2>
                                <div class="group-item">
                                    <div class="item-key col-3">PHP Verstion</div>
                                    <div class="item-value col-9"><?=phpversion()?></div>
                                </div>

                                <div class="group-item">
                                    <div class="item-key col-3">BrightMoon Verstion</div>
                                    <div class="item-value col-9"><?=app()::VERSION?></div>
                                </div>

                                <div class="group-item">
                                    <div class="item-key col-3">App Environment</div>
                                    <div class="item-value col-9"><?=env('APP_ENV', 'local')?></div>
                                </div>

                                <div class="group-item">
                                    <div class="item-key col-3">Debug mode</div>
                                    <div class="item-value col-9"><?=env('APP_DEBUG', 'true')?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </body>
</html>