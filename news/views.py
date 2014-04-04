from django.shortcuts import render, get_list_or_404
from news.models import Article

def articleList(request):
	articles = Article.objects.all()
	return render(request, 'news/articles.html', {'articles' : articles})

def categoryArticles(request, cat_name):
	articles = get_list_or_404(Article, category__name__iexact = cat_name)
	return render(request, 'news/articles.html', {'articles' : articles}) 
