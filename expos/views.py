from django.http import HttpResponse
from django.shortcuts import render


def static(request, page):
    return render(request, page + '.html')