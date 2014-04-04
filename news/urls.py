from django.conf.urls import patterns, include, url

urlpatterns = patterns('',
    url(r'^$', 'news.views.articleList', name = 'actus_home'),
    url(r'^category/([^\/]+)/$', 'news.views.categoryArticles', name = 'actus_cat'),
)
