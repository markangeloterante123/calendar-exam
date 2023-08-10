export default function(to, from, savedPosition) {
      if (to.path !== from.path) {
        document.body.scrollTo(0, 0);
        return { x: 0, y: 0 };
      }
    }
