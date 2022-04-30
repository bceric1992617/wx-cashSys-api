<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>点餐二维码下载</title>
        <link rel="stylesheet" type="text/css" href="{{asset('css/public.css')}}"></link>
        <style>
            .storeName, .tableNum {
                text-align:center;
                font-size:24px;
                font-weight:500;
                display:block;
                width:220px
            }
            .orderCodeImg {
                width:200px;
                height:200px
            } 
            ul {
                width:100%;
                float:left
            }
            .orderCode li {
                /* width: 33%; */
                float:left;
                border:1px solid #000
            }

            .pages li {
                float:left;
                margin-right:20px;
            }

            .pages {
                margin:0;
                padding:0
            }

        </style>
    </head>
    <body>
        <ul class='pages'>
            @for($i=1; $i <= $pageNum; $i++)
                <li><a href="{{$i}}">第{{$i}}页</a></li>
            @endfor
        </ul>
        <ul class='orderCode'>
            @foreach($tableInfo as $v)
                <li>
                    <span class='storeName'>{{$storeName}}</span>
                    <span class='tableNum'>{{$v->tableNum}}号桌</span>
                    <img class='orderCodeImg' src={{asset('/').$v->orderCodeAddr}}>
                </li>
            @endforeach
        </ul>
    </body>
</html>
