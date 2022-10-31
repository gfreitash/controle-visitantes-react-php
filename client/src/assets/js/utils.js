export function toTitleCase(str) {
    return str.toLowerCase().replace(/(^\w)|([-\s]\w)/g, (match) => match.toUpperCase());
}
