# SelectBox Link Field Plus

## What does this extension do?

This extension extends the functionality of the
[Selectbox Link Field](https://github.com/symphonycms/selectbox_link_field)-extension.

The extended functionality is:

 - It adds a button to the selectbox link field to create a new entry on-the-fly.
 - It provides the possibility to use different views, depending on what you are using it for (see 'View').

## Requirements

This extension requires that you have the
[Selectbox Link Field](https://github.com/symphonycms/selectbox_link_field)-extension in your extension folder,
since it extends those classes. Just having the directory in your extensions-folder is enough, you don't have to
enable it.

## Different views

Different situations require different views. For example, if you want to connect entries from a news-section or a
blog-section, you want to display them differently than if you would use it for an image gallery. See the different
views in the views-folder for how views are handled and how you can create your own views.

Created a new view? Share it with us and make this extension better!

## Filter items

Given `Albums` and `Images`; an `Album` can hold many `Images`.

`Images`:
1. Title (text)
2. Image path (upload)

`Albums`
1. Title (text)
2. Images (SBL+ with gallery)

The problem is that not all `Images` should be selectable for all `Albums`. Say an `Album` and think of selecting 2-3 images from a pool of 100+ images ...

1) In `Images` section, add another SBL+ which points to `Albums -> Title`.

`Images`:
1. Title (text)
2. Image path (upload)
3. **Visibility in Albums** (SBL+ with checkboxes(prefferable) / default / etc)

2) For every `Image` entry, choose the `Albums` in which this `Image` will appear.
3) Go to `Albums` section -> `Images` field settings and set `Filter the values` to `Images -> Visibility for albums`.
4) Navigate to an `Album` and feel the difference.
