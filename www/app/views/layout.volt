<!DOCTYPE html>
<html lang="en">
<head> 
    <meta charset="UTF-8">
    {% block title %}<title>A/B Test平台</title>{% endblock %}
    <link href="/css/common.css" rel="stylesheet">
    {% block head_script %}{% endblock %}
</head>
<body> 
   
    {% block content %}{% endblock %}

    <script type="text/javascript" src="/js/lib/jquery.js"></script>
    <script type="text/javascript" src="/js/lib/react.js"></script>
    <script type="text/javascript" src="/js/lib/react-dom.js"></script>
    <script type="text/javascript" src="/js/common.js"></script>
    {% block foot_script %}{% endblock %}
</body>
</html>
