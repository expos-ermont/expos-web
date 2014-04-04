from django.contrib import admin
from news.models import Category,Article,Comment

class ArticleAdmin(admin.ModelAdmin):
	list_display = ('title', 'category', 'author', 'pub_date', 'mod_date')
	list_filter = ('category','author','pub_date')
	search_fields = ['title']

admin.site.register(Category)
admin.site.register(Article, ArticleAdmin)
admin.site.register(Comment)
