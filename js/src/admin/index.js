// Export extend array for Flarum 2.0 Admin extender system
// Get the Admin extender constructor from compat
const Admin = flarum.core.compat['common/extenders'].Admin;

export const extend = [
  new Admin().setting(function () {
      const tags = flarum.core.app.store.all('tags');
      const selectedTags = JSON.parse(
        this.setting('bilgi42-autofollow-tags.tag_ids')() || '[]'
      );

      return (
        <div className="Form-group">
          <label>
            {flarum.core.app.translator.trans(
              'bilgi42-autofollow-tags.admin.tags_label',
              {},
              'Select tags to auto-follow for new users'
            )}
          </label>
          <div className="helpText">
            {flarum.core.app.translator.trans(
              'bilgi42-autofollow-tags.admin.tags_help',
              {},
              'New users will automatically follow these tags'
            )}
          </div>

          <div className="TagSelection">
            {tags.map((tag) => {
              const isSelected = selectedTags.includes(tag.id());

              return (
                <label className="checkbox" key={tag.id()}>
                  <input
                    type="checkbox"
                    checked={isSelected}
                    onchange={(e) => {
                      let newSelected = [...selectedTags];
                      if (e.target.checked) {
                        newSelected.push(tag.id());
                      } else {
                        newSelected = newSelected.filter((id) => id !== tag.id());
                      }
                      this.setting('bilgi42-autofollow-tags.tag_ids')(
                        JSON.stringify(newSelected)
                      );
                      m.redraw();
                    }}
                  />
                  <span style={{ color: tag.color() }}>{tag.name()}</span>
                </label>
              );
            })}
          </div>
        </div>
      );
    })
];
