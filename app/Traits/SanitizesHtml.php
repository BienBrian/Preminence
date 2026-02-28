<?php

namespace App\Traits;

trait SanitizesHtml
{
    protected function purify($html)
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,b,i,u,strong,em,a[href|title],ul,ol,li,h1,h2,h3,h4,h5,h6,blockquote,img[src|alt|width|height],table,thead,tbody,tr,td,th,span[style],div,hr,sub,sup,pre,code');
        $config->set('HTML.AllowedAttributes', 'src,href,alt,title,width,height,style,class');
        $config->set('CSS.AllowedProperties', 'font-weight,font-style,text-decoration,text-align,color,background-color,font-size,margin,padding');
        $config->set('AutoFormat.RemoveEmpty', true);
        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }
}
