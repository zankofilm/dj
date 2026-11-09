@php
$siteLocale=app()->getLocale();
$brandNames=$siteAppearance['brand']??['fa'=>'بازارگاه','ku'=>'بازاڕگا','en'=>'Marketplace'];
$brandName=data_get($brandNames,$siteLocale,data_get($brandNames,'fa','بازارگاه'));
$siteContact=$siteAppearance['contact']??[];
$footerLinks=data_get($siteAppearance,'navigation.footer',[]);
@endphp
<footer class="market-footer">
    <div class="market-footer-main">
        <div><strong>{{ $brandName }}</strong><span>{{ data_get($siteAppearance,'footer_tagline.'.$siteLocale,data_get($siteAppearance,'footer_tagline.fa','')) }}</span></div>
        <nav class="market-footer-links" aria-label="پیوندهای مفید">
            @foreach($footerLinks as $link)
                <a href="{{ $link['url'] }}">{{ data_get($link,'labels.'.$siteLocale,data_get($link,'labels.fa','')) }}</a>
            @endforeach
        </nav>
        <div class="market-footer-contact">
            @if(!empty($siteContact['email']))<a href="mailto:{{ $siteContact['email'] }}">{{ $siteContact['email'] }}</a>@endif
            @if(!empty($siteContact['phone']))<a href="tel:{{ $siteContact['phone'] }}">{{ $siteContact['phone'] }}</a>@endif
            @if(!empty($siteContact['instagram']))<a href="{{ $siteContact['instagram'] }}" target="_blank" rel="noopener noreferrer">Instagram</a>@endif
            @if(!empty($siteContact['telegram']))<a href="{{ $siteContact['telegram'] }}" target="_blank" rel="noopener noreferrer">Telegram</a>@endif
        </div>
    </div>
    <small>{{ data_get($siteAppearance,'footer_copyright.'.$siteLocale,data_get($siteAppearance,'footer_copyright.fa','')) }}</small>
</footer>
