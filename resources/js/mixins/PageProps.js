// These are the `shared` props from Inertia that are given to every page
// Without including some of the props, Vue will display a warning about
// non-prop attributes that are given but can't be used
export default {
  props: [
    'tenant',
    'psEnabled',
    'errors',
    'user',
    'school',
    'locales',
    'locale',
    'flash',
    'mainNav',
    'subNav',
    'breadcrumbs',
    'title',
  ]
}
