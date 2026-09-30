# Team page shortcode

`[Nova-Stats-Team-Page]` (`src/Shortcode/TeamPage.php`, template
`templates/shortcodes/team-page.php`) renders a `<nova-stats-team-page>` element. Every attribute is
optional and per instance, so a page can hold several team pages.

| Shortcode attribute | Element attribute   | Purpose                                                              |
| ------------------- | ------------------- | -------------------------------------------------------------------- |
| `league`            | `league-id`         | OpenDXP league ID. Team + division come from the league's first season. |
| `division`          | `division-id`       | hockeydata division ID.                                              |
| `team`              | `team-id`           | hockeydata team ID.                                                  |
| `image_path`        | `player-image-path` | Base path/URL for player images.                                     |

```
[Nova-Stats-Team-Page league="12"]
```

`league` is what the client portal generates: it stays valid across season changes, while
`team`/`division` change per season. When `team` or `division` is set they win over `league`. The
resolution itself happens in the widget, see `naba-hdwp-widgets/docs/team-page-league-id.md`.
