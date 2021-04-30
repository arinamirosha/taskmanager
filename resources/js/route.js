var routes = require('./routes.json');

export default function () {
    var args = Array.prototype.slice.call(arguments); // копируем arguments, не работает напрямую
    var name = args.shift();

    if (routes[name] === undefined) {
        console.log('Error: route doesn\'t exists.');
    } else {

        return '/' +
            routes[name]
                .split('/')
                .map(str => str[0] === '{' ? args.shift() : str )
                .join('/');
    }
}
