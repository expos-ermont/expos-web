from django.db import models

# Create your models here.
class Category(models.Model):
	name = models.CharField(max_length = 50)

	def __unicode__(self):
		return u'{0}'.format(self.name)

	class Meta:
		verbose_name_plural = 'categories'

class Article(models.Model):
	title = models.CharField(max_length = 100)
	category = models.ForeignKey(Category)
	pub_date = models.DateField('published date')
	mod_date = models.DateField('modification date')
	author = models.CharField(max_length = 50, editable = False)
	content = models.TextField()

	def __unicode__(self):
		return u'[{0}] {1}'.format(self.category.name, self.title)

	class Meta:
		ordering = ['-pub_date', 'title']

class Comment(models.Model):
        article = models.ForeignKey(Article)
        ip = models.GenericIPAddressField()
        author = models.CharField(max_length = 50)
        content = models.TextField()
        datetime = models.DateTimeField()
