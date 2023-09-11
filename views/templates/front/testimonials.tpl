{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{extends file='layouts/layout-full-width.tpl'}
{block name='cms_content'}
    {$cms.content nofilter}
{/block}

{block name='content'}
    <section id="egiotestimonials-forms" class="egiotestimonials-forms d-flex align-items-center justify-content-center">
        <div class="card card-block" style="max-width: 450px;">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title" class="form-control-label">{l s='TITLE' mod='egiotestimonials'} *</label>
                    <input type="text" class="form-control" id="title" name="title" maxlength="60" required>
                </div>

                <div class="form-group">
                    <label for="image" class="form-control-label">{l s='IMAGE' mod='egiotestimonials'}</label>
                    <input type="file" class="form-control-file" accept=".png,.jpeg,jpg" id="image" name="image" />
                </div>

                <div class="form-group">
                    <label for="message" class="form-control-label">{l s='MESSAGE' mod='egiotestimonials'} *</label>
                    <textarea name="message" class="form-control" rows="7" maxlength="300" required></textarea>
                </div>
                <div class="text-center mt-2">
                    <button type="submit" name="addtestimonialsubmit" class="btn btn-save">{l s='ADD NEW TESTIMONIAL' mod='egiotestimonials'}</button>
                </div>
            </form>
        </div>
    </section>
    <section id="testimonials-list" class="testimonials-list my-3">
        <h2 class="testimonials-list-head">{l s='Testimonials' mod='egiotestimonials'}</h2>
        <div class="row mt-3">
            {foreach from=$testimonials item=$testimonial}
                <div class="col-12 col-md-3">
                    <div class="testimonial">
                        {if isset($testimonial['image']) && !empty($testimonial['image'])}
                            <img class="img-fluid" src="{$image_path}/{$testimonial['image']}" title="{$testimonial['title']}" />
                        {/if}
                        <div class="testimonial-title font-weight-bold mt-1">
                            {$testimonial['title']}
                        </div>
                        <div class="testimonial-message">
                            {$testimonial['message']}
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </section>
{/block}